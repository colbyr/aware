<?php

use \Mockery as m;

class AwareModelTest extends PHPUnit_Framework_TestCase {

  function testErrorsMethod() {
    $model = m::mock('alias:Awareness\Aware');
  }

}