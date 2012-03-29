# Aware Model
Self validating models for Laravel 3.1's built-in Eloquent ORM

## Installation

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

## What's new in 2.0.0?
1. eloquent 2 / Laravel 3.1 support
2. removed temporary attributes
3. overridable **onSave** function

### If something is broken...
* Aware 2.0.0 only supports Laravel 3.1, if you're using Laravel <= 3.0 download [version 1.2](https://github.com/crabideau5691/aware/tags)
* Remember Aware no longer supports temporary attributes! if a validation rule isn't used every time put it in the controller

## Guide

* [Basic](#basic)
* [Validation](#validation)
* [Retrieving Errors](#errors)
* [Overriding Validation](#override)
* [onSave](#onsave)
* [Custom Error Messages](#messages)
* [Custom Validation Rules](#rules)

<a name="basic"></a>
## Basic

Aware aims to extend the Eloquent model without changing its core functionality. All Eloquent models are compatible with Aware.

To create a new Aware model, simply extend the Aware class: 

`class User extends Aware {}`

<a name="validation"></a>
## Validation

Aware models use Laravel's built-in [Validator class](http://laravel.com/docs/validation). Defining validation rules for a model is simple:

```php
class User extends Aware {

  /**
   * Aware validation rules
   */
  public static $rules = array(
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

**note:** You also can validate a model at an time using the `Aware->valid()` method.

<a name="errors"></a>
## Retrieving Errors

When an Aware model fails to validate, a Laravel\Messages object is attached to the Aware object.

Retrieve all errors with `Aware->errors->all()`.

Retrieve errors for a *specific* attribute using `Aware->errors->get('attribute')`.

**note:** Aware leverages Laravel's Messages object which has an [simple and elegant method](http://laravel.com/docs/validation#retrieving-error-messages) of formatting errors

<a name="overide"></a>
## Overriding Validation

There are two ways to override Aware's validation:

#### 1. Force Save
`force_save()` validates the model but saves regardless of whether or not there are errors

#### 2. Override Rules and Messages
both `Aware->save($rules, $messages)` and `Aware->valid($rules, $messages)` take to parameters

`$rules` is an array of Validator rules of the same form as `Aware->rules`. The same is true of the `$messages` parameter.

An array that is **not** empty will override the rules or messages specified by the class for that instance of the method only.

**note:** the default value for `$rules` and `$messages` is `array()`, if you pass an `array()` nothing will be overriden

<a name="onsave"></a>
## onSave

Aware provides a convenient method for performing actions when either `$model->save()` is called. For example, use `onSave` to automatically hash a users password:

```php
class User extends Aware {

  public function onSave()
  {
    // if there's a new password, hash it
    if($this->changed('password'))
    {
      $this->password = Hash::make($this->password);
    }

    return true;
  }

}
```

Notice that `onSave` returns a boolean. If you would like to halt `save`, return false.

**Note:** `force_save()` has it's own `onForceSave()` method, which behaves just like `onSave`.

### Overriding onSave

Just like, `$rules` and `$messages`, `onSave` can be overridden at call time. Simply pass a closure to the save function.

```
$user-save(array(), array(), function ($model) {
  echo "saving!";
  return true;
});
```
**Note:** the closure should have one parameter as it will be passed a reference to the model being saved.

<a name="messages"></a>
## Custom Error Messages

Just like the Laravel Validator, Aware lets you set custom error messages using the [same sytax](http://laravel.com/docs/validation#custom-error-messages).

```php
class User extends Aware {

  /**
   * Aware Messages
   */
  public static $messages = array(
    'required' => 'The :attribute field is required.'
  );

  ...

}
```

<a name="rules"></a>
## Custom Validation Rules

You can create custom validation rules the [same way](http://laravel.com/docs/validation#custom-validation-rules) you would for the Laravel Validator.

