<?php

namespace Krautoload;

/**
 * Warning:
 *   Including files during discovery can be risky,
 *   because the class might extend another class which is not available.
 *   We rather leave this task to a parser.
 */
abstract class InjectedAPI_ClassFileVisitor_IncludeEachAbstract extends InjectedAPI_ClassFileVisitor_Abstract {

  /**
   * @inheritdoc
   */
  function fileWithClass($file, $relativeClassName) {
    include_once $file;
    $this->includedFileWithClassCandidate($file, $relativeClassName);
  }

  /**
   * @inheritdoc
   */
  function fileWithClassCandidates($file, array $relativeClassNames) {
    include_once $file;
    foreach ($relativeClassNames as $relativeClassName) {
      $this->includedFileWithClassCandidate($file, $relativeClassName);
    }
  }

  /**
   * @inheritdoc
   */
  protected function includedFileWithClassCandidate($file, $relativeClassName) {
    if (class_exists($class = $this->getNamespace() . $relativeClassName, FALSE)) {
      $this->confirmedFileWithClass($file, $class);
    }
    elseif (interface_exists($class, FALSE)) {
      $this->confirmedFileWithInterface($file, $class);
    }
    elseif (function_exists('trait_exists') && trait_exists($class, FALSE)) {
      $this->confirmedFileWithTrait($file, $class);
    }
  }

  /**
   * @param string $file
   * @param string $class
   */
  abstract protected function confirmedFileWithClass($file, $class);

  /**
   * @param string $file
   * @param string $interface
   */
  protected function confirmedFileWithInterface($file, $interface) {
    // Do nothing by default.
  }

  /**
   * @param string $file
   * @param string $trait
   */
  protected function confirmedFileWithTrait($file, $trait) {
    // Do nothing by default.
  }
}
