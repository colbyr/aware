# Aware Model
Self validating models for Laravel PHP (extends [Eloquent ORM](https://github.com/taylorotwell/eloquent))

## Installation

### Dependencies
1. [Eloquent ORM](https://github.com/taylorotwell/eloquent)

### Artisan
`php artisan bundle:install aware`

### Bundle Registration
add the following to **application/bundles.php**

```php
'aware' => array(
  'autoloads' => array(
    'map' => array(
      'Aware' => '(:bundle)/model.php'
    ),
  )
),
```

## Guide

* [Basic](#basic)
* [Validation](#validation)
* [Retrieving Errors](#errors)
* [Overriding Validation](#temp)
* [Temporary Attributes](#temp)
* [Custom Error Messages](#messages)
* [Custom Validation Rules](#rules)

<a name="#basic"></a>
### Basic

Aware aims to extend the Eloquent model without changing its core functionality. All Eloquent models are compatible with Aware.

To create a new Aware model, simply extend the Aware class: 

`class User extends Aware {}`

<a name="#validation"></a>
### Validation

Aware models use Laravel's built-in [Validator class](http://laravel.com/docs/validation). Defining validation rules for a model is simple:

```php
class User extends Aware {

  /**
   * Aware validation rules
   */
  public $rules = array(
    'name' => 'required',
    'email' => 'required|email'
  );

  ...

}
```

Aware models validate themselves automatically when `Aware->save()` is called.

```php
$user = new User();
$user->name = 'Colby';
$user->email = 'crabideau5691@gmail.com';
$user->save(); // returns false if model is invalid
```

**note:** You also can validate a model at an time using the `Aware->validate()` method.

<a name="#errors"></a>
### Retrieving Errors

When an Aware model fails to validate, a array of error messages from the Laravel Validator are attached to the Aware object.

Retrieve all errors with `Aware->errors`.

Retrieve errors for a *specific* attribute using `Aware->errors_for('attribute')`.

By default, `errors_for` returns an array, but flagging the `$get_html` parameter `Aware->errors_for('attribute', true)` tells Aware to return an HTML formatted list.

<a name="#overide"></a>
### Overriding Validation

There are two ways to override Aware's validation:

#### 1. Force Save
`force_save()` validates the model but saves regardless of whether or not there are errors

#### 2. Override Rules and Messages
both `Aware->save($rules, $messages)` and `Aware->validate($rules, $messages)` take to parameters

`$rules` is an array of Validator rules of the same form as `Aware->rules`. The same is true of the `$messages` parameter.

An array that is **not** empty will override the rules or messages specified by the class for that instance of the method only.

**note:** the default value for `$rules` and `$messages` is `array()`, if you pass an `array()` nothing will be overriden

<a name="#temp"></a>
### Temporary Attributes

Aware also provides a convenient way to ignore attributes which may be necessary for validation but should not be saved to the database. Just include the attribute key in the temporary array

```php
class User extends Aware {

  /**
   * Aware Temporary Attributes
   */
  public $temporary = array('password_confirmation');

  ...

}
```

<a name="#messages"></a>
### Custom Error Messages

Just like the Laravel Validator, Aware lets you set custom error messages using the [same sytax](http://laravel.com/docs/validation#custom-error-messages).

```php
class User extends Aware {

  /**
   * Aware Messages
   */
  public $messages = array(
    'required' => 'The :attribute field is required.'
  );

  ...

}
```

<a name="#rules"></a>
### Custom Validation Rules

You can create custom validation rules the [same way](http://laravel.com/docs/validation#custom-validation-rules) you would for the Laravel Validator.

