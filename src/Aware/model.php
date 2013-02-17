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
        $data = $this->get_dirty();
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
   * on_save
   *  called evertime a model is saved - to halt the save, return false
   *
   * @return bool
   */
  public function on_save() {
    return true;
  }

  /**
   * on_force_save
   *  called evertime a model is force_saved - to halt the force_save, return false
   *
   * @return bool
   */
  public function on_force_save() {
    return true;
  }

  /**
   * Save
   *
   * @param array $rules:array
   * @param array $messages
   * @param closure $on_save
   * @return Aware|bool
   */
  public function save($rules=array(), $messages=array(), $on_save=null) {
    // evaluate on_save
    $before = is_null($on_save) ? $this->on_save() : $on_save($this);

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
  public function force_save($rules=array(), $messages=array(), $on_force_save=null) {
    // execute on_force_save
    $before = is_null($on_force_save) ? $this->on_force_save() : $on_force_save($this);

    // validate the model
    $this->validate($rules, $messages);

    // save regardless of the result of validation
    return $before ? parent::save() : false;
  }

}