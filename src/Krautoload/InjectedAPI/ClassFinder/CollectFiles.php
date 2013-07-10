<?php

namespace Krautoload;

class InjectedAPI_ClassFinder_CollectFiles extends InjectedAPI_ClassFinder_Abstract {

  protected $files = array();

  /**
   * Return all files collected during one class finding operation.
   *
   * @return array
   *   Associative array, where the keys are collected file names, and the
   *   values are booleans indicating whether the file can be expected to define
   *   the class we are looking for.
   */
  function getCollectedFiles() {
    return $this->files;
  }

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    $this->files[$file] = TRUE;
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    $this->files[$file] = FALSE;
  }
}
