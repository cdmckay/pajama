<?php

require_once __DIR__ . '/../../pajama-validator.php';

$rules = json_decode(file_get_contents(__DIR__ . '/rules.json'), true);

// Create the validator.
$validator = PajamaValidator::validate(array(
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