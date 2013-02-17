<?php

use \Mockery as m;
use Awareness\Aware;

class AwareModelTest extends PHPUnit_Framework_TestCase {

  function testErrorsMethod() {
    $model = m::mock('alias:Model');
    $real_model = new Model();
  }

}