<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_CollectExistingClasses extends InjectedAPI_ClassFileVisitor_IncludeEachAbstract {

  protected $classes = array();

  function getCollectedClasses() {
    return $this->classes;
  }

  function confirmedFileWithClass($file, $class) {
    $this->classes[$class] = $class;
  }
}
