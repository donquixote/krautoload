<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_CollectExistingClasses extends InjectedAPI_ClassFileVisitor_IncludeEachAbstract {

  protected $classes = array();

  /**
   * @return array
   *   The classes collected.
   *   For convenience, the class names are both the keys and the values of
   *   the array.
   */
  function getCollectedClasses() {
    return $this->classes;
  }

  /**
   * @inheritdoc
   */
  function confirmedFileWithClass($file, $class) {
    $this->classes[$class] = $class;
  }
}
