<?php

require_once __DIR__ . '/../../pajama-validator.php';

use \Pajama\Validator;
use \Pajama\ValidatorContext;

Validator::addMethod('alphanumeric', function(ValidatorContext $context, $value) {
    return $context->optional($value) || ctype_alnum($value);
});

$rules = json_decode(file_get_contents(__DIR__ . '/rules.json'), true);

// Create the validator.
$validator = Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
));

$response = array();
if ($validator->model()) {
    $response['successful'] = true;
} else {
    $response['successful'] = false;
}

header('Content-Type: application/json');
echo json_encode($response);