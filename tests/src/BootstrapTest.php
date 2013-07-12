<?php

namespace Krautoload\Tests;

use Krautoload as k;

class BootstrapTest extends \PHPUnit_Framework_TestCase {

  function testBootstrap() {
    $this->runTestScript('testrun.php');
  }

  function testBootstrapApc() {
    if (extension_loaded('apc') && function_exists('apc_store')) {
      $this->runTestScript('testrun-apc.php');
    }
  }

  protected function runTestScript($script, $expected = 'SUCCESS') {
    ob_start();
    system('php tests/scripts/' . $script);
    $response = ob_get_clean();
    $this->assertEquals('SUCCESS', $response);
  }
}