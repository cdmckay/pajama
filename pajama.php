<?php

namespace Pajama;

/**
 * A class for validating array models, typically the <code>$_POST</code> or <code>$_GET</code> superglobals.
 */
final class Validator {

    /**
     * @var array The model being validated.
     */
    private $model;

    /**
     * @var array The normalized rules for this Validator instance.
     */
    private $rules;

    /**
     * @var ValidatorContext The context for this Validator instance.
     */
    private $context;

    /**
     * @var array An array containing all the validation methods.
     */
    private static $methods = array();

    /**
     * Creates a new Validator instance.
     *
     * @param array $model The model to validate.
     * @param array $rules The rules the model is validated against.
     */
    private function __construct($model, $rules) {
        $this->model = self::flatten($model);
        $this->rules = $this->normalizeRules($rules);
        $this->context = new ValidatorContext($this);
    }

    /**
     * Flatten an array, appending all sub-array keys into the top-level name.
     *
     * For example, `$a['foo']['bar'] = 'baz'` becomes `$a['foo[bar]'] = 'baz'`.
     *
     * @param array $model
     * @return array
     */
    private static function flatten($model) {
        $repeat = false;
        foreach ($model as $name => $value) {
            if (is_array($value)) {
                $repeat = true;
                foreach ($value as $sub_name => $sub_value) {
                    $model["{$name}[$sub_name]"] = $sub_value;
                }
                unset($model[$name]);
            }
        }
        if ($repeat) {
            $model = self::flatten($model);
        }
        return $model;
    }

    /**
     * Validates the model, returning a reusable Validator object in the process.
     *
     * @param array $options
     * @return Validator
     */
    public static function validate($options) {
        $noop = function() {};
        $valid_handler = $options['validHandler'] ?: $noop;
        $invalid_handler = $options['invalidHandler'] ?: $noop;

        $validator = new Validator($options['model'], $options['rules']);
        if ($validator->model()) {
            $valid_handler($validator);
        } else {
            $invalid_handler($validator);
        }

        return $validator;
    }

    /**
     * Normalizes a rule array, removing all rules that are false.
     *
     * @param array $rules
     * @return array
     */
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

    /**
     * Normalizes a rule, converting string rules into their array equivalents.
     *
     * @param array|string $value
     * @return array
     */
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

    /**
     * Validates a field, returning true if valid, false otherwise.
     *
     * This is the rough equivalent of the "element" method in the jQuery Validation plugin.
     *
     * @param string $name
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function model() {
        $valid = true;
        foreach ($this->rules as $name => $rule) {
            $valid = $valid && $this->field($name);
            if (!$valid) break;
        }
        return $valid;
    }

    /**
     * @return array
     */
    public function invalids() {
        $invalids = array();
        foreach ($this->rules as $name => $rule) {
            if (!$this->field($this->model, $name)) {
                $invalids[] = $name;
            }
        }
        return $invalids;
    }

    /**
     * @return int
     */
    public function numberOfInvalids() {
        return count($this->invalids($this->model));
    }

    /**
     * @return array
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return array
     */
    public function getRules() {
        return $this->rules;
    }

    /**
     * @return array
     */
    public static function getMethods() {
        return self::$methods;
    }

    /**
     * Adds a new validation method.
     *
     * @param string $method_name The name of the method, i.e. 'range' or 'creditcard'.
     * @param callable $method The validation method, typically an anonymous function.
     */
    public static function addMethod($method_name, $method) {
        self::$methods[$method_name] = $method;
    }

}

/**
 * Provided to each validation method to provide useful methods and validation context information.
 */
final class ValidatorContext {

    private $validator;

    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    public function optional($value) {
        return is_null($value) || $value === "";
    }

    public function parseSelector($selector) {
        $selector = str_replace(array('\[', '\]'), array('[', ']'), $selector);
        $result = null;
        if (preg_match('/^#([A-Za-z][\w\-\.]*)(:\w+)?$/', $selector, $matches) ||
            preg_match('/^\[name=([\w\-\.\[\]]+)\](:\w+)?$/', $selector, $matches)) {
            $result = array(
                'name' => $matches[1],
                'pseudo-class' => $matches[2],
            );
        }
        return $result;
    }

    public function resolve($value, $param) {
        $result = false;
        if (is_bool($param)) {
            $result = $param;
        } else if (is_string($param)) {
            $result = $this->resolveExpression($param);
        } else if (is_callable($param)) {
            $result = $param($this->validator->getModel(), $value);
        }
        return $result;
    }

    public function resolveExpression($expression) {
        // Supports the selectors:
        // #X
        // [name=X]
        // with pseudo-classes:
        // :checked
        // :unchecked
        // :filled
        // :blank
        $result = false;
        $parts = $this->parseSelector($expression);
        if ($parts !== null) {
            $name = $parts['name'];
            $pseudo_class = $parts['pseudo-class'];
            $model = $this->validator->getModel();
            switch ($pseudo_class) {
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
                    // No pseudo-class.
                    $result = array_key_exists($name, $model);
                    break;
                default:
                    // Unsupported pseudo-class.
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
    return $required ? !$context->optional($value) : true;
});

Validator::addMethod('minlength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context->optional($value) || $length >= $param;
});

Validator::addMethod('maxlength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context->optional($value) || $length <= $param;
});

Validator::addMethod('rangelength', function(ValidatorContext $context, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $context->optional($value) || $length >= $param[0] && $length <= $param[1];
});

Validator::addMethod('min', function(ValidatorContext $context, $value, $param) {
    return $context->optional($value) || $value >= $param;
});

Validator::addMethod('max', function(ValidatorContext $context, $value, $param) {
    return $context->optional($value) || $value <= $param;
});

Validator::addMethod('range', function(ValidatorContext $context, $value, $param) {
    return $context->optional($value) || $value >= $param[0] && $value <= $param[1];
});

Validator::addMethod('email', function(ValidatorContext $context, $value) {
    return $context->optional($value) || filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
});

Validator::addMethod('url', function(ValidatorContext $context, $value) {
    if ($context->optional($value)) {
        return true;
    }

    $has_permitted_protocol =
        substr($value, 0, 4) === 'http'  ||
        substr($value, 0, 5) === 'https' ||
        substr($value, 0, 3) === 'ftp';

    return $has_permitted_protocol && filter_var($value, FILTER_VALIDATE_URL) !== false;
});

Validator::addMethod('date', function(ValidatorContext $context, $value) {
    return $context->optional($value) || strtotime($value) !== false;
});

Validator::addMethod('dateISO', function(ValidatorContext $context, $value) {
    return $context->optional($value) || preg_match('/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/', $value);
});

Validator::addMethod('number', function(ValidatorContext $context, $value) {
    return $context->optional($value) || is_numeric($value);
});

Validator::addMethod('digits', function(ValidatorContext $context, $value) {
    return $context->optional($value) || preg_match('/^\d+$/', $value);
});

Validator::addMethod('creditcard', function(ValidatorContext $context, $value) {
    if ($context->optional($value)) {
        return true;
    }
    if (preg_match('/[^0-9 \-]+/', $value)) {
        return false;
    }

    $value = preg_replace('/\/D/', '', $value);
    $check = 0;
    $even = false;
    for ($n = strlen($value) - 1; $n >= 0; $n--) {
        $digit = intval($value[$n]);
        if ($even && ($digit *= 2) > 9) {
            $digit -= 9;
        }
        $check += $digit;
        $even = !$even;
    }
    return ($check % 10) === 0;
});

Validator::addMethod('equalTo', function(ValidatorContext $context, $value, $param) {
    if ($context->optional($value)) {
        return true;
    }

    $valid = false;
    $parts = $context->parseSelector($param);
    if ($parts !== null) {
        $name = $parts['name'];
        $model = $context->getValidator()->getModel();
        $valid = $value === $model[$name];
    }
    return $valid;
});

