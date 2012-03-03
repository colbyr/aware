# Aware Model
Self validating models for Laravel PHP (extends [Eloquent ORM](https://github.com/taylorotwell/eloquent))

## Installation

### Dependencies
1. [Eloquent ORM](https://github.com/taylorotwell/eloquent)

### Bundle Registration
add the following to **application/bundles.php**

```php
'aware' => array(
  'autoloads' => array(
  'map' => array(
    'Aware'    => '(:bundle)/model.php'
  ),
),
```
## Guide

* [Basic](#basic)
* [Validation](#validation)
* [Retrieving Errors](#errors)
* [Temporary Attributes](#temp)
* [Custom Error Messages](#messages)

<a href="#basic"></a>
### Basic

Aware aims to extend the Eloquent model without changing its core functionality. All Eloquent models are compatible with Aware.

To create a new Aware model, simply extend the Aware class: 

`class User extends Aware {}`

<a href="#validation"></a>
### Validation

Aware models use Laravel's built-in Validator class. Defining validation rules for a model is simple:

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

<a href="#errors"></a>
### Retrieving Errors

When an Aware model fails to validate, a array of error messages from the Laravel Validator are attached to the Aware object.

Retrieve all errors with `Aware->errors`.

Retrieve errors for a *specific* attribute using `Aware->errors_for('attribute')`.

By default, `errors_for` returns an array, but flagging the `$get_html` parameter `Aware->errors_for('attribute', true)` tells Aware to return an HTML formatted list.

<a href="#temp"></a>
### Temporary Attributes

Aware also provides a convenient way to ignore attributes which may be necessary for validation but should not be saved to the database. Just include the attribute key in the temporary array

```php
class User extends Aware {

  /**
   * Aware Temporary Attributes
   */
  public $temporary = array('password_confirmation');

}
```

<a href="#messages"></a>
### Retrieving Errors

