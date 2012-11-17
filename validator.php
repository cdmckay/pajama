<?php

class Validator {

    protected $model;
    protected $rules;
    protected static $methods = array();

    protected function __construct($model, $rules) {
        $this->model = $model;
        $this->rules = $rules;
    }

    public static function validate($options) {
        return new Validator($options['model'], $options['rules']);
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
            $valid = $valid && $method($value, $param);
            if (!$valid) break;
        }
        return $valid;
    }

    public function numberOfInvalids() {
        $count = 0;
        foreach ($this->rules as $name => $rule) {
            if (!$this->field($name)) {
                $count++;
            }
        }
        return $count;
    }

}

Validator::addMethod('required', function($value, $param) {
    $required = is_bool($param) ? $param : $param();
    return $required ? isset($value) : true;
});
