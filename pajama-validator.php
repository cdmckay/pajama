<?php

class PajamaValidator {

    protected $model;
    protected $rules;
    protected static $methods = array();

    protected function __construct($model, $rules) {
        $this->model = $model;
        $this->rules = $rules;
        foreach ($this->rules as $name => $value) {
            $this->rules[$name] = $this->normalizeValue($value);
        }
    }

    private function normalizeValue($value) {
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
        return new PajamaValidator($options['model'], $options['rules']);
    }

    public static function getMethods() {
        return self::$methods;
    }

    public static function addMethod($method_name, $method) {
        self::$methods[$method_name] = $method;
    }

    public static function optional($value) {
        return is_null($value) || $value === "";
    }

    public function depend($param, $value) {
        $result = null;
        if (is_bool($param)) {
            $result = $param;
        } else if (is_string($param)) {
            // Do nothing.  We don't want strings recognized as a callable because we might use this
            // later for checking name dependencies.
        } else if (is_callable($param)) {
            $result = $param($this->model, $value);
        }
        return $result;
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
            $valid = $valid && (is_null($method) || $method($this, $value, $param));
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

PajamaValidator::addMethod('required', function(PajamaValidator $validator, $value, $param) {
    $required = $validator->depend($param, $value);
    return $required ? !$validator::optional($value) : true;
});

PajamaValidator::addMethod('minlength', function(PajamaValidator $validator, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $validator::optional($value) ?: $length >= $param;
});

PajamaValidator::addMethod('maxlength', function(PajamaValidator $validator, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $validator::optional($value) ?: $length <= $param;
});

PajamaValidator::addMethod('rangelength', function(PajamaValidator $validator, $value, $param) {
    $length = is_array($value) ? count($value) : strlen($value);
    return $validator::optional($value) ?: $length >= $param[0] && $length <= $param[1];
});

PajamaValidator::addMethod('equalTo', function(PajamaValidator $validator, $value, $param) {
    // The parameter must not be empty, must be at least 2 characters, and start with a #.
    if ($validator::optional($param) || strlen($param) === 1 || $param[0] !== "#") {
        return true;
    }

    $model = $validator->getModel();
    $name = substr($param, 1);
    return $value === $model[$name];
});

