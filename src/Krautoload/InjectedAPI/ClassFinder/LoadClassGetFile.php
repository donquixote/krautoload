<?php

namespace Krautoload;

/**
 * Some methods are overridden only to avoid one level of indirection...
 */
class InjectedAPI_ClassFinder_LoadClassGetFile extends InjectedAPI_ClassFinder_Abstract {

  protected $file = FALSE;

  /**
   * @return string
   *   The file where the class was finally found.
   */
  function getFile() {
    return $this->file;
  }

  /**
   * @inheritdoc
   */
  function guessFile($file) {
    if (is_file($file)) {
      include $file;
      $this->file = $file;
      return TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate($file) {
    if (is_file($file)) {
      include_once $file;
      if (class_exists($this->className, FALSE)
        || interface_exists($this->className, FALSE)
        || (function_exists('trait_exists') && trait_exists($this->className, FALSE))
      ) {
        $this->file = $file;
        return TRUE;
      }
    }
  }

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    require $file;
    $this->file = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    require_once $file;
    if (class_exists($this->className, FALSE)
      || interface_exists($this->className, FALSE)
      || (function_exists('trait_exists') && trait_exists($this->className, FALSE))
    ) {
      $this->file = $file;
      return TRUE;
    }
  }
}
