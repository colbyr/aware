<?php

use Eloquent\Model as Model;

abstract class Aware extends Model
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
   * Temporary Attributes
   */
  public $temp = array();

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
  public function errors_for($field, $get_html=false)
  {
    $es = $this->errors->messages[$field];
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
   * Set Attribute as temporary
   *    temporary attribtues aren't saved to the database
   *
   *    e.g. a password confirmation field
   */
  public function temporary($attribute)
  {
    if(is_array($attribute)){
      foreach($attribute as $attr){
        if($this->$attr){
          $this->temp[$attr] = $this->$attr;
          unset($this->$attr);
        }
      }
    }else{
      if($this->$attribute){
        $this->temp[$attribute] = $this->$attribute;
        unset($this->$attribute);
      }
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

      $data = array_merge($this->attributes, $this->temp);

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

}

/**
 * Model Exception
 */
class ModelException extends Exception {}