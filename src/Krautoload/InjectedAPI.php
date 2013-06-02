<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
class InjectedAPI {

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
   * Suggest a file that, if the file exists,
   * HAS TO declare the class we are looking for.
   * Include that file.
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
      return TRUE;
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
   *   TRUE, if the file exists and it defines the file.
   *   FALSE, otherwise.
   */
  function guessFileCandidate($file) {
    if (is_file($file)) {
      include_once $file;
      return class_exists($this->className, FALSE);
    }
    return FALSE;
  }

  /**
   * Same as guessFile(), but skip the is_file(),
   * assuming that we already know the file exists.
   *
   * This is useful if a plugin already did the is_file() check by itself.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   *
   * @return boolean
   *   TRUE, if the file exists and it defines the file.
   *   FALSE, never.
   */
  function claimFile($file) {
    require $file;
    return TRUE;
  }

  function claimFileCandidate($file) {
    require_once $file;
    return class_exists($this->className, FALSE);
  }

  /**
   * Same as suggestFile(), but check the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   */
  function guessFile_checkIncludePath($file) {
    if ($this->fileExistsInIncludePath($file)) {
      include $file;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Same as suggestFile(), but check the full PHP include path.
   *
   * @param string $file
   *   The file that is supposed to declare the class.
   */
  function guessFileCandidate_checkIncludePath($file) {
    if ($this->fileExistsInIncludePath($file)) {
      include_once $file;
      return class_exists($this->className, FALSE);
    }
    return FALSE;
  }

  /**
   * Check if a file exists, considering the full include path.
   *
   * @param string $file
   *   The filepath
   * @return boolean
   *   TRUE, if the file exists somewhere in include path.
   */
  protected function fileExistsInIncludePath($file) {
    if (function_exists('stream_resolve_include_path')) {
      // Use the PHP 5.3.1+ way of doing this.
      return (FALSE !== stream_resolve_include_path($file));
    }
    elseif ($file{0} === DIRECTORY_SEPARATOR) {
      // That's an absolute path already.
      return file_exists($file);
    }
    else {
      // Manually loop all candidate paths.
      foreach (explode(PATH_SEPARATOR, get_include_path()) as $base_dir) {
        if (file_exists($base_dir . DIRECTORY_SEPARATOR . $file)) {
          return TRUE;
        }
      }
      return FALSE;
    }
  }
}
