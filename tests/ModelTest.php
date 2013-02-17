<?php

use \Mockery as m;

class ModelTest extends PHPUnit_Framework_TestCase {

  static function setUpBeforeClass() {
    spl_autoload_call('\Awareness\Aware\Model');
  }

  function tearDown() {
    m::close();
  }

  function testErrorsMethod() {
    $model = static::genModel();
    $this->assertInstanceOf('\Illuminate\Support\MessageBag', $model->errors());
  }

  static function genModel() {
    return m::mock('\Awareness\Aware\Model[]');
  }

}