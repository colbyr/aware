<?php namespace Awareness/Aware;

use Illuminate\Database;
use Illuminate\Support\Contracts\MessageProviderInterface;
use Illuminate\Support\MessageBag;
use Illuminate\Validation;

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Model extends Eloquent\Model implements MessageProviderInterface {

  private static
    $validator = null;

  protected
    $error_bag;

  public static
    /**
     * Aware Validation Messages
     *
     * @var array $messages
     */
    $messages = array(),

    /**
     * Aware Validation Rules
     *
     * @var array $rules
     */
    $rules = array();

  function __construct($attributes=array(), $exists=false) {
    parent::__construct($attributes, $exists);
    $this->error_bag = new MessageBag();
  }

  function errors() {
    return $this->error_bag;
  }

  function getMessageBag() {
    return $this->errors();
  }

  function getValidationInfo($rules_override=null, $messages_override=null) {

    $data = $this->exists ? $this->getDirty() : $this->attributes;

    $rules = array_intersect_key($rules_override ?: static::$rules, $data);

    return count($rules) > 0 ?
      array($data, $rules, $messages_override ?: static::$messages) :
      array(null, null, null);
  }

  /**
   * Validate the Model
   *    runs the validator and binds any errors to the model
   *
   * @param array $rules
   * @param array $messages
   * @return bool
   */
  function isValid($rules_override=null, $messages_override=null) {
    $valid = true;
    list($data, $rules, $messages) = $this->getValidationInfo($rules_override);

    if ($rules) {
      $validator = Validator::make($data, $rules, $messages);
      $valid = $validator->passes();
    }

    if (!$valid) {
      $this->error_bag = $validator->errors();
    } else if ($this->error_bag->any()) {
      $this->error_bag = new MessageBag();
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
  function __set($key, $value) {
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
  function onSave() {
    return true;
  }

  /**
   * Called evertime a model is forceSaved - to halt the forceSave, return false
   *
   * @return bool
   */
  function onForceSave() {
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
  function save($rules=array(), $messages=array(), $onSave=null) {
    // evaluate onSave
    $before = is_null($onSave) ? $this->onSave() : $onSave($this);

    // check before & valid, then pass to parent
    return !($before && $this->isValid($rules, $messages)) ?: parent::save();
  }

  /**
   * Force Save
   *    attempts to save model even if it doesn't validate
   *
   * @param $rules:array
   * @param $messages:array
   * @return Aware|bool
   */
  function forceSave($rules=array(), $messages=array(), $onForceSave=null) {
    // execute onForceSave
    $before = is_null($onForceSave) ? $this->onForceSave() : $onForceSave($this);

    // validate the model
    $this->isValid($rules, $messages);

    // save regardless of the result of validation
    return $before ? parent::save() : false;
  }

}