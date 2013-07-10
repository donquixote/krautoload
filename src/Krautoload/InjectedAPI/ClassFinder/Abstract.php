<?php

namespace Krautoload;

abstract class InjectedAPI_ClassFinder_Abstract implements InjectedAPI_ClassFinder_Interface {

  protected $className;

  /**
   * @param $class_name
   *   Name of the class or interface we are trying to load.
   */
  function __construct($class_name) {
    $this->className = $class_name;
  }

  /**
   * Get the name of the class we are looking for.
   *
   * @return string
   *   The class we are looking for.
   */
  function getClass() {
    return $this->className;
  }

  /**
   * @inheritdoc
   */
  function guessFile($file) {
    if (is_file($file)) {
      return $this->claimFile($file);
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate($file) {
    if (is_file($file)) {
      return $this->claimFileCandidate($file);
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  function guessFile_checkIncludePath($file) {
    if (FALSE !== $file = Util::findFileInIncludePath($file)) {
      return $this->claimFile($file);
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate_checkIncludePath($file) {
    if (FALSE !== $file = Util::findFileInIncludePath($file)) {
      return $this->claimFileCandidate($file);
    }
    return FALSE;
  }
}
