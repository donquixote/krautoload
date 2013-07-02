<?php

namespace Krautoload;

class DiscoveryAPI_CollectClasses implements DiscoveryAPI_Interface {

  protected $classes;

  function getCollectedClasses() {
    return $this->classes;
  }

  function fileWithClass($file, $class) {
    $this->classes[$class] = TRUE;
  }

  function fileWithClassCandidates($file, $classes) {
    include_once $file;
    foreach ($classes as $class) {
      if (class_exists($class, FALSE)) {
        $this->classes[$class] = TRUE;
      }
    }
  }
}
