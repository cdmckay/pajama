<?php

namespace Pajama;

class Validator {

    protected $model;
    protected $rules;
    protected $context;
    protected static $methods = array();

    protected function __construct($model, $rules) {
        $this->model = $model;
        $this->rules = $this->normalizeRules($rules);
        $this->context = new ValidatorContext($this);
    }

    private function normalizeRules($rules) {
        foreach ($rules as $field => $rule) {
            $normalized_rule = $this->normalizeRule($rule);
            foreach ($normalized_rule as $method_name => $param) {
                if ($param === false) {
                    unset($normalized_rule[$method_name]);
                }
            }
            $rules[$field] = $normalized_rule;
        }
        return $rules;
    }

    private function normalizeRule($value) {
        $normalized_value = $value;
        if (is_string($value)) {
            $normalized_value = array();
            $method_names = preg_split('/\s/', $value);
            foreach ($method_names as $method_name) {
                $normalized_value[$method_name] = true;
            }
        }
        return $normalized_value;
    }

    public static function validate($options) {
        return new Validator($options['model'], $options['rules']);
    }

    public static function getMethods() {
        return self::$methods;
    }

    public static function addMethod($method_name, $method) {
        self::$methods[$method_name] = $method;
    }

    public function model() {
        $valid = true;
        foreach ($this->rules as $name => $rule) {
            $valid = $valid && $this->field($name);
            if (!$valid) break;
        }
        return $valid;
    }

    public function field($name) {
        $value = $this->model[$name];
        $rule = $this->rules[$name];
        $valid = true;
        foreach ($rule as $method_name => $param) {
            $method = self::$methods[$method_name];
            $valid = $valid && (is_null($method) || $method($this->context, $value, $param));
            if (!$valid) break;
        }
        return $valid;
    }

    public function numberOfInvalids($model) {
        $count = 0;
        foreach ($this->rules as $name => $rule) {
            if (!$this->field($model, $name)) {
                $count++;
            }
        }
        return $count;
    }

    public function getModel() {
        return $this->model;
    }

    public function getRules() {
        return $this->rules;
    }

}

class ValidatorContext {

    private $validator;

    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    public static function optional($value) {
        return is_null($value) || $value === "";
    }

    public function resolve($value, $param) {
        $result = false;
        if (is_bool($param)) {
            $result = $param;
        } else if (is_string($param)) {
            $result = $this->resolveSelector($param);
        } else if (is_callable($param)) {
            $result = $param($this->validator->getModel(), $value);
        }
        return $result;
    }

    public function resolveSelector($selector) {
        // Should support:
        // #X
        // [name=X]
        // with
        // :checked
        // :unchecked
        // :filled
        // :blank
        $result = false;
        $matches = array();
        if (preg_match('/^#([A-Za-z][\w\-]*)(:\w+)?$/', $selector, $matches) ||
            preg_match('/^\[name=([\w\-]+)\](:\w+)?$/', $selector, $matches)) {
            list(, $name, $pseudo_class) = $matches;
            $model = $this->validator->getModel();
            switch ($matches[2]) {
                case "checked":
                    $result = array_key_exists($name, $model);
                    break;
                case "unchecked":
                    $result = !array_key_exists($name, $model);
                    break;
                case "filled":
                    $result = array_key_exists($name, $model) && strlen($model[$name]) > 0;
                    break;
                case "blank":
                    $result = array_key_exists($name, $model) && strlen($model[$name]) === 0;
                    break;
                case null:
                    // No selector.
                    $result = array_key_exists($name, $model);
                    break;
                default:
                    // Unsupported selector.
                    $result = array_key_exists($name, $model);
                    break;
            }
        }
        return $result;
    }

    public function getValidator() {
        return $this->validator;
    }

}

Validator::addMethod('required', function(ValidatorContext $context, $value, $param) {
    $required = $context->resolve($value, $param);
    return $required ? !$context::optional($value) : true;
});

Validator::addMethod('minlength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context::optional($value) ?: $length >= $param;
});

Validator::addMethod('maxlength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context::optional($value) ?: $length <= $param;
});

Validator::addMethod('rangelength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context::optional($value) ?: $length >= $param[0] && $length <= $param[1];
});

Validator::addMethod('email', function(ValidatorContext $context, $value) {
    return $context::optional($value) ?: filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
});

Validator::addMethod('url', function(ValidatorContext $context, $value) {
    return $context::optional($value) ?: filter_var($value, FILTER_VALIDATE_URL) !== false;
});

Validator::addMethod('equalTo', function(ValidatorContext $context, $value, $param) {
    // The parameter must not be empty, must be at least 2 characters, and start with a #.
    // TODO Make this work with [name=X] as well.
    if ($context::optional($param) || strlen($param) === 1 || $param[0] !== "#") {
        return true;
    }

    $model = $context->getValidator()->getModel();
    $name = substr($param, 1);
    return $value === $model[$name];
});

