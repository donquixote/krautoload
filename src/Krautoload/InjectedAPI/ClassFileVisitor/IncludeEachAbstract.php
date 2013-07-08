<?php

namespace Krautoload;

/**
 * Warning:
 *   Including files during discovery can be risky,
 *   because the class might extend another class which is not available.
 *   We rather leave this task to a parser.
 */
abstract class InjectedAPI_ClassFileVisitor_IncludeEachAbstract extends InjectedAPI_ClassFileVisitor_Abstract {

  function fileWithClass($file, $relativeClassName) {
    include_once $file;
    if (class_exists($class = $this->getClassName($relativeClassName), FALSE)) {
      $this->confirmedFileWithClass($file, $class);
    }
    elseif (interface_exists($class, FALSE)) {
      $this->confirmedFileWithInterface($file, $class);
    }
  }

  function fileWithClassCandidates($file, $relativeClassNames) {
    include_once $file;
    foreach ($relativeClassNames as $relativeClassName) {
      // Only find classes, not interfaces.
      if (class_exists($class = $this->getClassName($relativeClassName), FALSE)) {
        $this->confirmedFileWithClass($file, $class);
      }
      elseif (interface_exists($class, FALSE)) {
        $this->confirmedFileWithInterface($file, $class);
      }
    }
  }

  abstract protected function confirmedFileWithClass($file, $class);

  protected function confirmedFileWithInterface($file, $interface) {
    // Do nothing by default.
  }
}
