<?php

use Laravel\Messages, Eloquent\Model;

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Aware extends Model
{

  /**
   * Aware Validation Rules
   *
   * @var Array $rules
   */
  public static $rules = array();

  /**
   * Aware Validation Messages
   *
   * @var Array $messages
   */
  public static $messages = array();

  /**
   * Attrubutes Aware shouldn't save to the database
   *
   * @var Array $temporary
   */
  public $temporary = array();

  public $ignore = array();

  /**
   * Aware Errors
   *
   * @var Laravel\Messages $errors
   */
  public $errors;

  /**
   * Create new Aware instance
   *
   * @param Array $attributes
   * @return void
   */
  public function __construct($attributes = array())
  {
    // initialize empty messages object
    $this->errors = new Messages();
    parent::__construct($attributes);
  }

  /**
   * Validate the Model
   *    runs the validator and binds any errors to the model
   *
   * @param $rules:array
   * @param $messages:array
   * @return bool
   */
  public function valid($rules=array(), $messages=array())
  {

    // innocent until proven guilty
    $valid = true;

    if(!empty($rules) || !empty(static::$rules))
    {

      // merge model dirty attributes and ignored values for validation
      $data = array_merge($this->get_dirty(), $this->ignore);

      // check for overrides
      $rules = (empty($rules)) ? static::$rules : $rules;
      $messages = (empty($messages)) ? static::$messages : $messages;

      // if the model exists, this is an update, so just validate the fields
      // that are being updated
      if ($this->exists) {
        $rules = array_intersect_key($rules, $data);
      }

      // construct the validator
      $validator = Validator::make($data, $rules, $messages);
      $valid = $validator->valid();

      // if the model is valid, unset old errors
      if($valid)
      {
        $this->errors->messages = array();
      }
      else // otherwise set the new ones
      {
        $this->errors = $validator->errors;
      }

    }

    return $valid;
  }

  /**
   * Magic Method for setting Aware attributes.
   *    - Handles temporary attributes then delegates to Eloquent
   *
   * @param $key
   * @param $value
   * @return void
   */
  public function __set($key, $value)
  {
    // If the key is flagged as temporary, add it to the ignored attributes.
    // Ignored attributes are not stored in the database.
    if (in_array($key, $this->temporary))
    {
      $this->ignore[$key] = $value;
    }
    else
    {
      // why bother setting it if it's the same value?
      // doing this solves the problem of validating unique fields against
      // themselves
      if (!array_key_exists($key, $this->attributes) || $value !== $this->attributes[$key])
      {
        parent::__set($key, $value);
      }
    }
  }

  /**
   * Save
   *
   * @param $rules:array
   * @param $messages:array
   * @return bool
   */
  public function save($rules=array(), $messages=array())
  {
    return ($this->valid($rules, $messages)) ? parent::save() : false;
  }

  /**
   * Force Save
   *    attempts to save model even if it doesn't validate
   *
   * @param $rules:array
   * @param $messages:array
   * @return bool
   */
  public function force_save($rules=array(), $messages=array())
  {

    // validate the model
    $this->valid($rules, $messages);

    // save regardless of the result of validation
    return parent::save();

  }

}