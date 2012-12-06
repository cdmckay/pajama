Pajama
======

Pajama provides drop-in server-side validation for your existing [jQuery Validation plugin](http://bassistance.de/jquery-plugins/jquery-plugin-validation/) forms.
It can also be used for standalone server-side validation in a pinch.

Since the goal of Pajama was to be used with the jQuery Validation plugin, the API has been designed to
be similar and thus familiar to developers who already know how to use the jQuery Validation plugin.

For more information on using the Pajama API, please [refer to the documentation](http://cdmckay.org/pajama/docs/namespaces/Pajama.html).

For documentation on the validation methods (like required, min, max, etc.) refer to the
[jQuery Validation plugin](http://docs.jquery.com/Plugins/Validation#List_of_built-in_Validation_methods) documentation.

## Getting Started (jQuery Validation integration)

First, get [jQuery](http://code.jquery.com/jquery-latest.js) and the [jQuery Validation plugin](http://bassistance.de/jquery-plugins/jquery-plugin-validation/).

Next, create a `rules.json` file:

```javascript
{
    "rating":{
        "required":true,
        "range":[1, 100]
    }
}
```

Now, include jQuery and the jQuery Validation plugin on a page:

```html
<html>
    <head>
        <title>Getting Started</title>
        <script type="text/javascript" src="jquery.js"></script>
        <script type="text/javascript" src="jquery.validate.js"></script>
    </head>
    <body>
        <form method="post" action="form-handler.php">
            <label>Rating: <input type="text" name="rating" /></label>
        </form>
        <script type="text/javascript">
        $.getJSON("rules.json", function(rules) {
            $("form").validate({ rules: rules });
        });
        </script>
    </body>
</html>
```

Finally, create a PHP script to handle the form submission:

```php
require_once 'pajama.php';

\Pajama\Validator::validate(array(
    'model' => $_POST,
    'rules' => json_decode('rules.json', true),
    'validHandler' => function() {
        // Store rating in database.
    },
));
```

For a more detailed example, see the `jquery-validation-integration` example in the `examples` folder.

## Getting Started (standalone)

Although not its primary design goal, Pajama can be used standalone.
Simply include the `pajama.php` file in your PHP file and pass it a model and some rules, like so:

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

// Validate with callbacks...
$validator = \Pajama\Validator::validate(array(
    'model' => $_POST,
    'rules' => $rules,
    'validHandler' => function() {
        // Model validated.
    },
    'invalidHandler' => function() {
        // Model failed validation.
    },
));

// ...or methods.
if($validator->model()) {
    // Model validated.
} else {
    // Model failed validation.
}
```

See the `standalone` example in the `examples` folder.

## Custom Validators

Like the jQuery Validation plugin, Pajama can be extended via custom validators. To create a custom validator, use the
[addMethod](http://cdmckay.org/pajama/docs/classes/Pajama.Validator.html#method_addMethod) static method like so:

```php
\Pajama\Validator::addMethod('regex', function($validator, $value, $param)) {
    return $this->optional($value) || preg_match('/' . $param . '/', $value);
});

\Pajama\Validator::validate(array(
    'model' => $_POST,
    'rules' => array(
        'md5_hash' => array(
            'required' => true,
            'regex' => '/^[A-Fa-f0-9]+$/',
        ),
    ),
    'validHandler' => function() {
        // ...
    },
));
```

Remember that if you're using Pajama with the jQuery Validation plugin, you must also write a JavaScript version of
the validation method:

```javascript
$.validator.addMethod("regex", function(value, element, param) {
    return this.optional(element) || new RegExp(param).test(value);
}, "This field does not conform to a pattern.");
```

## Limitations

Since Pajama has no access to the submitting form's DOM context, it only has limited support for CSS selectors.

For example, consider this mark-up:

```html
<form>
    <div class="checkbox-group">
        <label><input type="checkbox" name="foo[0]" value="bar" />Bar</label>
        <label><input type="checkbox" name="foo[1]" value="baz" />Baz</label>
    </div>
</form>
```


The jQuery Validation plugin [required](http://docs.jquery.com/Plugins/Validation/Methods/required)
validation method can support arbitrary jQuery selectors like this:

```javascript
{
    "email": {
        "required":".checkbox-group :checkbox:first:checked"
        "email":true
    }
}
```

However, the best Pajama can do is this:

```javascript
{
    "email": {
        "required":"[name=foo[0]]:checked"
        "email":true
    }
}
```

In general, Pajama can only recognize selectors with the form `#foo` or `[name=foo]` and the following pseudo-classes:

* `:checked`
* `:unchecked`
* `:filled`
* `:blank`

In the case of `#foo`, Pajama assumes that the `name` and the `id` of the element were the same, as in:

```html
<input type="text" name="foo" id="foo" />
```
