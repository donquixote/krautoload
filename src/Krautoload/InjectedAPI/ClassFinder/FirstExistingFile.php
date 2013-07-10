<?php

namespace Krautoload;

/**
 * Whenever a file is found, this api object will think it's the right one,
 * and remember it to be returned with getFile().
 */
class InjectedAPI_ClassFinder_FirstExistingFile extends InjectedAPI_ClassFinder_Abstract {

  protected $file = FALSE;

  /**
   * Return all files collected during one class finding operation.
   *
   * @return string
   *   The first file that was found.
   */
  function getFile() {
    return $this->file;
  }

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    $this->file = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    $this->file = $file;
    return TRUE;
  }
}
