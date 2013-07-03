<?php

namespace Krautoload;

class DiscoveryAPI_CollectClasses extends DiscoveryAPI_Abstract {

  protected $classes = array();

  function getCollectedClasses() {
    return $this->classes;
  }

  protected function confirmedFileWithClass($file, $class) {
    $this->classes[$class] = TRUE;
  }
}
