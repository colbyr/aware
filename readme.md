# Aware Model
Self validating models for Laravel PHP (extends [Eloquent ORM](https://github.com/taylorotwell/eloquent))

## Installation

### Dependencies
1. [Eloquent ORM](https://github.com/taylorotwell/eloquent)

### Bundle Registration
add the following to **application/bundles.php**

```php
'struct' => array(
  'autoloads' => array(
  'map' => array(
    'Struct'    => '(:bundle)/model.php'
  ),
),
```
