<?php

require_once __DIR__ . '/../../pajama.php';

use \Pajama\Validator;
use \Pajama\ValidatorContext;

Validator::addMethod('alphanumeric', function(ValidatorContext $context, $value) {
    return $context->optional($value) || ctype_alnum($value);
});

$rules = json_decode(file_get_contents(__DIR__ . '/rules.json'), true);

$response = array();
Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
    'validHandler' => function() use(&$response) {
        $response['successful'] = true;
    },
    'invalidHandler' => function() use(&$response) {
        $response['successful'] = false;
    },
));

header('Content-Type: application/json');
echo json_encode($response);