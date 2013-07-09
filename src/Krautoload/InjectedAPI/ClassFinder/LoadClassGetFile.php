<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
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
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   * Include that file, if it exists.
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
      include $file;
      $this->file = $file;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   * Include that file, if it exists.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists and the class exists after file inclusion.
   *   FALSE, otherwise.
   */
  function guessFileCandidate($file) {
    if (is_file($file)) {
      include_once $file;
      if (class_exists($this->className, FALSE) || interface_exists($this->className, FALSE) || trait_exists($class, FALSE)) {
        $this->file = $file;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Suggest a file that HAS TO declare the class we are looking for.
   * Include that file.
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
   *   Always TRUE, because we assume the file does exist and does define the
   *   class.
   */
  function claimFile($file) {
    require $file;
    $this->file = $file;
    return TRUE;
  }

  /**
   * Suggest a file that MAY declare the class we are looking for.
   * Include that file.
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
   *   TRUE, if the class exists after file inclusion.
   *   FALSE, otherwise
   */
  function claimFileCandidate($file) {
    require_once $file;
    if (class_exists($this->className, FALSE) || interface_exists($this->className, FALSE) || trait_exists($this->className, FALSE)) {
      $this->file = $file;
      return TRUE;
    }
  }

  /**
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   * Include that file, if it exists.
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
    if (FALSE !== $file = Util::findFileInIncludePath($file)) {
      include $file;
      $this->file = $file;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Suggest a file that, if the file exists,
   * MAY declare the class we are looking for.
   * Include that file, if it exists.
   *
   * Unlike guessFile(), this one checks the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists and the class exists after file inclusion.
   *   FALSE, otherwise.
   */
  function guessFileCandidate_checkIncludePath($file) {
    if (FALSE !== $file = Util::findFileInIncludePath($file)) {
      include_once $file;
      if (class_exists($this->className, FALSE) || interface_exists($this->className, FALSE) || trait_exists($this->className, FALSE)) {
        $this->file = $file;
        return TRUE;
      }
    }
    return FALSE;
  }
}
