<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
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
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists.
   *   FALSE, otherwise.
   */
  function guessFile($file) {
    if (is_file($file)) {
      $this->files[$file] = TRUE;
      return $file;
    }
    return FALSE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   Always FALSE, because we had no chance to check whether the file actually
   *   defines the class.
   */
  function guessFileCandidate($file) {
    if (is_file($file)) {
      $this->files[$file] = FALSE;
    }
    return FALSE;
  }

  /**
   * Suggest a file that HAS TO declare the class we are looking for.
   *
   * Unlike guessFile(), claimFile() being called means that the caller is sure
   * that the file does exist. Thus, we can skip the is_file() check, saving a
   * few nanoseconds.
   *
   * This is useful if a plugin already did the is_file() check by itself.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   Always TRUE, because further candidates are not interesting.
   */
  function claimFile($file) {
    $this->files[$file] = TRUE;
    return TRUE;
  }

  /**
   * Suggest a file that MAY declare the class we are looking for.
   *
   * Unlike guessFile(), claimFile() being called means that the caller is sure
   * that the file does exist. Thus, we can skip the is_file() check, saving a
   * few nanoseconds.
   *
   * This is useful if a plugin already did the is_file() check by itself.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   Always FALSE, because we had no chance to check whether the file actually
   *   defines the class.
   */
  function claimFileCandidate($file) {
    $this->files[$file] = FALSE;
    return FALSE;
  }

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists.
   *   FALSE, otherwise.
   */
  function guessFile_checkIncludePath($file) {
    if ($this->fileExistsInIncludePath($file)) {
      $this->files[$file] = TRUE;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   Always FALSE, because we had no chance to check whether the file actually
   *   defines the class.
   */
  function guessFileCandidate_checkIncludePath($file) {
    if ($this->fileExistsInIncludePath($file)) {
      $this->files[$file] = FALSE;
    }
    return FALSE;
  }
}
