<?php

abstract class Aware extends Eloquent\Model
{

  /**
   * Validation Rules
   */
  public $rules = array();

  /**
   * Validation Messages
   */
  public $messages = array();

  /**
   * List of attrubutes to be considered temporary
   */
  public $temporary = array();

  /**
   * Errors
   */
  public $errors;

  /**
   * is field dirty?
   */
  public function dirty($str)
  {
    return !empty($this->dirty[$str]);
  }

  /**
   * Get errors for field
   */
  public function errors_for($attribute, $get_html=false)
  {
    $es = $this->errors->messages[$attribute];
    if($get_html){
      $html = '';
      if(!empty($es)){
        $html .= '<ul class="errors">';
        foreach($es as $e)
        {
          $html .= '<li>' . $e . '</li>';
        }
        $html .= '</ul>';
      }
      return $html;
    }else{
      return $es;
    }
  }

  /**
   * Validate the Model
   *    runs the validator and binds any errors to the model
   *
   * @return bool
   */
  public function validate($rules=array(), $messages=array())
  {
    $valid = true;

    if(!empty($rules) || $this->rules){

      $data = array_merge($this->attributes, $this->ignore);

      $validator = Validator::make($data, (empty($rules)) ? $this->rules : $rules, (empty($rules)) ? $this->messages : $messages);
      $valid = $validator->valid();

      if($valid){
        unset($this->errors);
      }else{
        $this->errors = $validator->errors;
      }
    }

    return $valid;
  }

  /**
   * Magic Method for setting Aware attributes.
   *    - Handles temporary attributes then delegates to Eloquent
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
   */
  public function save($rules=array(), $messages=array())
  {
    $res; 
    if($this->validate($rules, $messages)){
      $res = parent::save();
    }else{
      $res = false;
    }
    return $res;
  }

  /**
   * Force Save
   *    attempts to save model even if it doesn't validate
   */
  public function force_save($rules=array(), $messages=array())
  {
    $this->validate($rules, $messages);
    return parent::save();
  }

}

/**
 * Model Exception
 */
class ModelException extends Exception {}