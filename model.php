<?php

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Aware extends Eloquent\Model
{

  /**
   * Aware Validation Rules
   * 
   * @var array
   */
  public static $rules = array();

  /**
   * Aware Validation Messages
   * 
   * @var array
   */
  public static $messages = array();

  /**
   * Attrubutes Aware shouldn't save to the database
   * 
   * @var array
   */
  public $temporary = array();

  /**
   * Errors
   * 
   * @var array
   */
  public $errors;

  /**
   * Dirty
   *    checks if attribute is dirty
   * 
   * @param $attribute:string
   * @return bool
   */
  public function dirty($attribute)
  {
    return !empty($this->dirty[$attribute]);
  }

  /**
   * Get errors for attribute
   * 
   * @param $attribute:string
   * @return array
   */
  public function errors_for($attribute)
  {
    return isset($this->errors->messages[$attribute]) ? $this->errors->messages[$attribute] : array();
  }

  /**
   * Convenience method for getting html list of errors
   * 
   * @param $attribute:string
   * @return string
   */
  public function html_errors_for($attribute)
  {

    // get any errors for the given attribute
    $errors = isset($this->errors->messages[$attribute]) ? $this->errors->messages[$attribute] : array();

    $html = '';

    // build html
    if(!empty($errors)){
      $html .= '<ul class="errors">';
      foreach($errors as $error)
      {
        $html .= '<li>' . $error . '</li>';
      }
      $html .= '</ul>';
    }

    return $html;

  }

  /**
   * Validate the Model
   *    runs the validator and binds any errors to the model
   *
   * @param $rules:array
   * @param $messages:array
   * @return bool
   */
  public function validate($rules=array(), $messages=array())
  {

    // innocent until proven guilty
    $valid = true;

    if(!empty($rules) || static::$rules){

      // merge model attributes and ignored values for validation
      $data = array_merge($this->attributes, $this->ignore);

      // check for overrides
      $rules = (empty($rules)) ? static::$rules : $rules;
      $messages = (empty($rules)) ? static::$messages : $messages;

      // construct the validator
      $validator = Validator::make($data, $rules, $messages);
      $valid = $validator->valid();

      // if the model is valid, unset old errors
      if($valid)
      {
        unset($this->errors);
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
      parent::__set($key, $value);
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
    if($this->validate($rules, $messages))
    {
      return parent::save();
    }
    else
    {
      return false;
    }
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
    $this->validate($rules, $messages);

    // save regardless of the result of validation
    return parent::save();

  }

}

/**
 * Model Exception
 */
class AwareException extends Exception {}