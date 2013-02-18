<?php

use \Mockery as m;

class ModelTest extends PHPUnit_Framework_TestCase {

  function tearDown() {
    m::close();
  }

  function testErrorsMethod() {
    $model = static::genModel();
    $this->assertInstanceOf('\Illuminate\Support\MessageBag', $model->getErrors());
  }

  static function genModel() {
    return m::mock('\Aware[]');
  }

}
