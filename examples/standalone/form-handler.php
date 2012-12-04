<?php

require_once __DIR__ . '/../../pajama.php';

use \Pajama\Validator;

$rules = array(
    'first_name' => 'required',
    'last_name' => 'required',
    'password_1' => array(
        'required' => true,
        'minlength' => 5,
        'equalTo' => '#password_2',
    ),
    'password_2' => array(
        'required' => true,
        'minlength' => 5,
        'equalTo' => '#password_1',
    ),
);

// Create the validator.
$validator = Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
));

$successful = $validator->model();

header('Location: form.php?successful=' . ($successful ? 1 : 0));
