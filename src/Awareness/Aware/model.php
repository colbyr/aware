<?php namespace Awareness/Aware;

use Illuminate\Database;
use Illuminate\Support\MessageBag;

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Model extends Eloquent\Model {

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
   * @var Illuminate\Support\MessageBag $errors
   */
  public $errors;

  /**
   * Create new Aware instance
   *
   * @param array $attributes
   * @return void
   */
  public function __construct($attributes = array(), $exists = false) {
    // initialize empty messages object
    $this->errors = new MessageBag();
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
  public function validate($rules=array(), $messages=array()) {
    // innocent until proven guilty
    $valid = true;

    if(!empty($rules) || !empty(static::$rules)) {
      // check for overrides
      $rules = (empty($rules)) ? static::$rules : $rules;
      $messages = (empty($messages)) ? static::$messages : $messages;

      // if the model exists, this is an update
      if ($this->exists) {
        // and only include dirty fields
        $data = $this->getDirty();
        // so just validate the fields that are being updated
        $rules = array_intersect_key($rules, $data);
      } else {
        // otherwise validate everything!
        $data = $this->attributes;
      }


      // construct the validator
      $validator = Validator::make($data, $rules, $messages);
      $valid = $validator->valid();

      // if the model is valid, unset old errors
      if($valid) {
        $this->errors->messages = array();
      } else { // otherwise set the new ones
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
  public function __set($key, $value) {
    // only update an attribute if there's a change
    if (!array_key_exists($key, $this->attributes) || $value !== $this->$key) {
      parent::__set($key, $value);
    }
  }

  /**
   * Called evertime a model is saved - to halt the save, return false
   *
   * @return bool
   */
  public function onSave() {
    return true;
  }

  /**
   * Called evertime a model is forceSaved - to halt the forceSave, return false
   *
   * @return bool
   */
  public function onForceSave() {
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
  public function save($rules=array(), $messages=array(), $onSave=null) {
    // evaluate onSave
    $before = is_null($onSave) ? $this->onSave() : $onSave($this);

    // check before & valid, then pass to parent
    return ($before && $this->validate($rules, $messages)) ? parent::save() : false;
  }

  /**
   * Force Save
   *    attempts to save model even if it doesn't validate
   *
   * @param $rules:array
   * @param $messages:array
   * @return Aware|bool
   */
  public function forceSave($rules=array(), $messages=array(), $onForceSave=null) {
    // execute onForceSave
    $before = is_null($onForceSave) ? $this->onForceSave() : $onForceSave($this);

    // validate the model
    $this->validate($rules, $messages);

    // save regardless of the result of validation
    return $before ? parent::save() : false;
  }

}