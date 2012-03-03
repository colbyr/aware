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


<a href="#errors"></a>
### Retrieving Errors


<a href="#temp"></a>
### Temporary Attributes


<a href="#messages"></a>
### Retrieving Errors