<?php

require_once __DIR__ . '/../../pajama.php';

use \Pajama\Validator;
use \Pajama\ValidatorContext;

Validator::addMethod('regex', function(ValidatorContext $context, $value, $param) {
    return $context->optional($value) || preg_match('/' . $param . '/', $value);
});

$rules = json_decode(file_get_contents(__DIR__ . '/rules.json'), true);

$response = array();
Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
    'validHandler' => function() use(&$response) {
        $response['successful'] = true;
    },
    'invalidHandler' => function(Validator $validator) use(&$response) {
        $response['successful'] = false;
        $response['invalid_field_names'] = array_keys($validator->invalidFields());
    },
));

header('Content-Type: application/json');
echo json_encode($response);