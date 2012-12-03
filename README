Pajama Validator - Client-Server Form Validation
================================================

The Pajama Validator provides drop-in server-side validation for your existing [jQuery Validation Plugin](http://bassistance.de/jquery-plugins/jquery-plugin-validation/) forms.
It can also be used for standalone server-side validation in a pinch.

Since the goal of the Pajama Validator was to be used with the jQuery Validation Plugin (JQVP), the API has been designed to
be similar and thus familiar to developers who already know how to use the JQVP.

## Getting Started (standalone)

Although not its primary design goal, the Pajama Validator can be used standalone.
Simply include the `pajama-validator.php` file in your PHP file and pass it a model and some rules, like so:

```php
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
$validator = \Pajama\Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
));

if($validator->model()) {
    // Model validated.
} else {
    // Model failed validation.
}
```

Also see the `server-side` example in the `examples` folder.

## Getting Started (jQuery Validation Plugin integration)

This will be filled in soon.
