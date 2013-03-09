<?php

require 'vendor/autoload.php';

use \Cdmckay\Pajama\Validator;

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

$successful = null;
Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
    'validHandler' => function() use(&$successful) {
        $successful = 1;
    },
    'invalidHandler' => function() use(&$successful) {
        $successful = 0;
    }
));

header('Location: form.php?successful=' . ($successful ? 1 : 0));
