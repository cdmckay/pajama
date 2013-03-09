<?php

/*
 * This file is part of the Pajama package.
 *
 * (c) Cameron McKay <me@cdmckay.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cdmckay\Pajama;

/**
 * Provided to each validation method to provide useful methods and validation context information.
 */
final class ValidatorContext {

    /**
     * @var Validator The Validator instance that this context is being provided for.
     */
    private $validator;

    /**
     * Creates a new ValidatorContext instance for a given Validator.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    /**
     * Tests whether the given value is null or an empty string.
     *
     * Example:
     * <code>
     * Validator::addMethod('example', function($context, $value) {
     *     return $context->optional($value) || ...;
     * }
     * </code>
     *
     * @param string $value The value to test.
     * @return bool True if the value is null or an empty string, false otherwise.
     */
    public function optional($value) {
        return is_null($value) || $value === '';
    }

    /**
     * Parses a Pajama-compatible selector into two parts.
     *
     * A Pajama-compatible selector has the format:
     *
     * - #foo
     * - #foo:bar
     * - [name=foo]
     * - [name=foo]:bar
     *
     * The two parts are:
     *
     * - <b>name</b> The field name in the selector.
     * - <b>pseudo-class</b> The pseudo-class portion of the selector.
     *
     * Example:
     * <code>
     * $context = ...;
     * $parts = $context->parseSelector('[name=foo]:checked]');
     * // $parts['name'] === 'foo'
     * // $parts['pseudo-class'] === 'checked'
     * </code>
     *
     * @param string $selector A Pajama-compatible selector.
     * @return array|null An array containing the two parts of the selector or null if the selector could not be parsed.
     */
    public function parseSelector($selector) {
        $selector = str_replace(array('\[', '\]'), array('[', ']'), $selector);
        $result = null;
        if (preg_match('/^#([A-Za-z][\w\-\.]*)(:\w+)?$/', $selector, $matches) ||
            preg_match('/^\[name=([\w\-\.\[\]]+)\](:\w+)?$/', $selector, $matches)) {
            $result = array(
                'name' => $matches[1],
                'pseudo-class' => isset($matches[2]) ? $matches[2] : null,
            );
        }
        return $result;
    }

    /**
     * Resolves the param into a boolean value.
     *
     * - If the <b>param</b> is a boolean, it will be returned untouched.
     * - If the <b>param</b> is a string, it will be parsed as a Pajama-compatible selector.
     * - If the <b>param</b> is a callable, it will be called with the passed <b>value</b>.
     *
     * @param string $value The value to be passed to the param if it is a callable.
     * @param bool|string|callable $param The parameter to resolve.
     * @return bool
     */
    public function resolve($value, $param) {
        $result = false;
        if (is_bool($param)) {
            $result = $param;
        } else if (is_string($param)) {
            $result = $this->resolveExpression($param);
        } else if (is_callable($param)) {
            $result = $param($this->validator, $value);
        }
        return $result;
    }

    /**
     * Resolve a Pajama-compatible selector expression based on the model values.
     *
     * @param string $expression
     * @return bool
     */
    private function resolveExpression($expression) {
        $result = false;
        $parts = $this->parseSelector($expression);
        if ($parts !== null) {
            $name = $parts['name'];
            $pseudo_class = $parts['pseudo-class'];
            $model = $this->validator->getModel();
            switch ($pseudo_class) {
                case "checked":
                    $result = array_key_exists($name, $model);
                    break;
                case "unchecked":
                    $result = !array_key_exists($name, $model);
                    break;
                case "filled":
                    $result = array_key_exists($name, $model) && strlen($model[$name]) > 0;
                    break;
                case "blank":
                    $result = array_key_exists($name, $model) && strlen($model[$name]) === 0;
                    break;
                case null:
                    // No pseudo-class.
                    $result = array_key_exists($name, $model);
                    break;
                default:
                    // Unsupported pseudo-class.
                    $result = array_key_exists($name, $model);
                    break;
            }
        }
        return $result;
    }

    /**
     * Returns the Validator instance associated with this context.
     *
     * @return Validator
     */
    public function getValidator() {
        return $this->validator;
    }

}
