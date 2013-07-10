<?php

namespace Krautoload;

class InjectedAPI_ClassFinder_FindExistingClass extends InjectedAPI_ClassFinder_LoadClass {

  /**
   * @inheritdoc
   */
  function guessFile($file) {
    return is_file($file);
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate($file) {
    return is_file($file);
  }

  /**
   * @inheritdoc
   */
  function claimFile($file) {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function claimFileCandidate($file) {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  function guessFile_checkIncludePath($file) {
    return Util::fileExistsInIncludePath($file);
  }

  /**
   * @inheritdoc
   */
  function guessFileCandidate_checkIncludePath($file) {
    return Util::fileExistsInIncludePath($file);
  }
}
