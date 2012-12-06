Pajama
======

Pajama provides drop-in server-side validation for your existing [jQuery Validation plugin](http://bassistance.de/jquery-plugins/jquery-plugin-validation/) forms.
It can also be used for standalone server-side validation in a pinch.

Since the goal of Pajama was to be used with the jQuery Validation plugin, the API has been designed to
be similar and thus familiar to developers who already know how to use the jQuery Validation plugin.

## Getting Started (jQuery Validation integration)

This will be filled in soon.

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

However, the best we could do in Pajama could do is this:

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
