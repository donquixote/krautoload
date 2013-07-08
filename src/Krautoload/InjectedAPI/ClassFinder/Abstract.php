<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
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
