<?php

namespace Krautoload\Tests;

use Krautoload as k;

class BootstrapTest extends \PHPUnit_Framework_TestCase {

  function testBootstrap() {
    $this->runTestScript('testrun.php');
  }

  function testBootstrapApc() {
    $this->runTestScript('testrun-apc.php');
  }

  protected function runTestScript($script, $expected = 'SUCCESS') {
    ob_start();
    system('php tests/scripts/' . $script);
    $response = ob_get_clean();
    $this->assertEquals('SUCCESS', $response);
  }
}