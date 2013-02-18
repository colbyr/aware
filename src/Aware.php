<?php

use \Illuminate\Database\Eloquent;
use \Illuminate\Support\Contracts\MessageProviderInterface;
use \Illuminate\Support\MessageBag;
use \Illuminate\Validation;

/**
 * Aware Models
 *    Self-validating Eloquent Models
 */
abstract class Aware extends Eloquent\Model implements MessageProviderInterface {

  protected

    /**
     * Error message container
     *
     * @var Illuminate\Support\MessageBag
     */
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

  /**
   * Returns the errors container
   *
   * @return Illuminate\Support\MessageBag
   */
  function getErrors() {
    if (!$this->error_bag) {
      $this->error_bag = new MessageBag();
    }
    return $this->error_bag;
  }

  /**
   * Returns attirbutes with updated values
   *
   * @return array
   */
  function getChanged() {
    return array_diff($this->attributes, $this->original);
  }

  /**
   * Returns the errors container
   *
   * @return Illuminate\Support\MessageBag
   */
  function getMessageBag() {
    return $this->errors();
  }

  /**
   * Returns rules and data that needs validating
   *
   * @return array
   */
  function getValidationInfo($rules_override=null, $messages_override=null) {

    if ($this->exists) {
      $data = $this->getChanged();
      $rules = array_intersect_key($rules_override ?: static::$rules, $data);
    } else {
      $data = $this->attributes;
      $rules = $rules_override ?: static::$rules;
    }

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
    } else if ($this->error_bag && $this->error_bag->any()) {
      $this->error_bag = new MessageBag();
    }

    return $valid;
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
   * Save the model if it is valid
   *
   * @param  array
   * @param  array
   * @param  closure
   * @return bool
   */
  function save($rules=array(), $messages=array(), $onSave=null) {
    // evaluate onSave
    $before = is_null($onSave) ? $this->onSave() : $onSave($this);

    // check before & valid, then pass to parent
    return ($before && $this->isValid($rules, $messages)) ? parent::save() : false;
  }

  /**
   * Attempts to save model even if it doesn't validate
   *
   * @param  array
   * @param  array
   * @return bool
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