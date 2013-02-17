<?php

use Laravel\Messages;

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Aware extends Eloquent
{

  /**
   * Aware Validation Rules
   *
   * @var array $rules
   */
  public static $rules = array();

  /**
   * Aware Validation Messages
   *
   * @var array $messages
   */
  public static $messages = array();

  /**
   * Aware Errors
   *
   * @var Laravel\Messages $errors
   */
  public $errors;

  /**
   * Create new Aware instance
   *
   * @param array $attributes
   * @return void
   */
  public function __construct($attributes = array(), $exists = false)
  {
    // initialize empty messages object
    $this->errors = new Messages();
    parent::__construct($attributes, $exists);
  }

  /**
   * Validate the Model
   *    runs the validator and binds any errors to the model
   *
   * @param array $rules
   * @param array $messages
   * @return bool
   */
  public function valid($rules=array(), $messages=array())
  {

    // innocent until proven guilty
    $valid = true;

    if(!empty($rules) || !empty(static::$rules))
    {

      // check for overrides
      $rules = (empty($rules)) ? static::$rules : $rules;
      $messages = (empty($messages)) ? static::$messages : $messages;

      // if the model exists, this is an update
      if ($this->exists)
      {
        // and only include dirty fields
        $data = $this->get_dirty();
        
        // so just validate the fields that are being updated
        $rules = array_intersect_key($rules, $data);
      }
      else
      {
        // otherwise validate everything!
        $data = $this->attributes;
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
   *    ignores unchanged attibutes delegates to Eloquent
   *
   * @param string $key
   * @param string|num|bool|etc $value
   * @return void
   */
  public function __set($key, $value)
  {

    // only update an attribute if there's a change
    if (!array_key_exists($key, $this->attributes) || $value !== $this->$key)
    {
      parent::__set($key, $value);
    }

  }

  /**
   * onSave
   *  called evertime a model is saved - to halt the save, return false
   *
   * @return bool
   */
  public function onSave()
  {
    return true;
  }

  /**
   * onForceSave
   *  called evertime a model is force_saved - to halt the force_save, return false
   *
   * @return bool
   */
  public function onForceSave()
  {
    return true;
  }

  /**
   * Save
   *
   * @param array $rules:array
   * @param array $messages
   * @param closure $onSave
   * @return Aware|bool
   */
  public function save($rules=array(), $messages=array(), $onSave=null)
  {

    // validate
    $valid = $this->valid($rules, $messages);

    // evaluate onSave
    $before = is_null($onSave) ? $this->onSave() : $onSave($this);

    // check before & valid, then pass to parent
    return ($before && $valid) ? parent::save() : false;

  }

  /**
   * Force Save
   *    attempts to save model even if it doesn't validate
   *
   * @param $rules:array
   * @param $messages:array
   * @return Aware|bool
   */
  public function force_save($rules=array(), $messages=array(), $onForceSave=null)
  {

    // validate the model
    $this->valid($rules, $messages);

    // execute onForceSave
    $before = is_null($onForceSave) ? $this->onForceSave() : $onForceSave($this);

    // save regardless of the result of validation
    return $before ? parent::save() : false;

  }

}