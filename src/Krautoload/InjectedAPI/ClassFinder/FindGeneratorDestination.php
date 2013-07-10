<?php

namespace Krautoload;

class InjectedAPI_ClassFinder_FindGeneratorDestination extends InjectedAPI_ClassFinder_Abstract {

  protected $destination;

  function getDestination() {
    return $this->destination;
  }

  /**
   * @inheritdoc
   */
  function guessFile($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    $this->destination = $file;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function guessFile_checkIncludePath($file) {
    // Include path is not supported when looking for a destination.
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate_checkIncludePath($file) {
    // Include path is not supported when looking for a destination.
    return FALSE;
  }
}
