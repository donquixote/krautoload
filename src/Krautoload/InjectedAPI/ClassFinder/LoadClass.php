<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
class InjectedAPI_ClassFinder_LoadClass extends InjectedAPI_ClassFinder_Abstract {

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    require $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    require_once $file;
    return class_exists($this->className, FALSE)
      || interface_exists($this->className, FALSE)
      || (function_exists('trait_exists') && trait_exists($this->className, FALSE))
    ;
  }
}
