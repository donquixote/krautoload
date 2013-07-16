<?php

namespace Krautoload;

/**
 * Collection of standalone static methods with no non-obvious side effects.
 */
class Util {

  /**
   * Determine if the class loader is called in a context where not loading a
   * class is "non-lethal".
   */
  static function calledFromClassExists() {
    foreach ($trace = debug_backtrace() as $i => $item) {
      if ($item['function'] === 'spl_autoload_call') {
        switch ($f = $trace[$i + 1]['function']) {
          case 'class_exists':
          case 'interface_exists':
          case 'method_exists':
          case 'trait_exists':
          case 'is_callable':
          // @todo Add more cases.
            return TRUE;
          default:
            return FALSE;
        }
      }
    }
  }

  static function classIsDefined($class) {
    return class_exists($class, FALSE)
      || interface_exists($class, FALSE)
      || (function_exists('trait_exists') && trait_exists($class, FALSE))
    ;
  }

  /**
   * Check if a file exists, considering the full include path.
   *
   * @param string $file
   *   The filepath
   * @return boolean
   *   TRUE, if the file exists somewhere in include path.
   *   FALSE, otherwise.
   */
  static function fileExistsInIncludePath($file) {
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

  /**
   * Check if a file exists, considering the full include path.
   *
   * @param string $file
   *   The filepath
   * @return boolean|string
   *   The resolved file path, if the file exists in the include path.
   *   FALSE, otherwise.
   */
  static function findFileInIncludePath($file) {
    if (function_exists('stream_resolve_include_path')) {
      // Use the PHP 5.3.1+ way of doing this.
      return stream_resolve_include_path($file);
    }
    elseif ($file{0} === DIRECTORY_SEPARATOR) {
      // That's an absolute path already.
      return file_exists($file) ? $file : FALSE;
    }
    else {
      // Manually loop all candidate paths.
      foreach (explode(PATH_SEPARATOR, get_include_path()) as $base_dir) {
        if (file_exists($base_dir . DIRECTORY_SEPARATOR . $file)) {
          return $base_dir . DIRECTORY_SEPARATOR . $file;
        }
      }
      return FALSE;
    }
  }

  /**
   * @param string $string
   *   The original string, that we want to explode.
   * @param boolean $lowercase
   *   should the result be lowercased?
   * @param string $example_string
   *   Example to specify how to deal with multiple uppercase characters.
   *   Can be something like "AA Bc" or "A A Bc" or "AABc".
   * @param boolean $glue
   *   Allows to implode the fragments with sth like "_" or "." or " ".
   *   If $glue is FALSE, it will just return an array.
   *
   * @throws \Exception
   * @return array|string
   *   An indexed array of pieces, if $glue is FALSE.
   *   A glued string, if $glue is a string.
   */
  static function camelCaseExplode($string, $lowercase = TRUE, $example_string = 'AA Bc', $glue = FALSE) {
    static $regexp_available = array(
      '/([A-Z][^A-Z]*)/',
      '/([A-Z][^A-Z]+)/',
      '/([A-Z]+[^A-Z]*)/',
    );
    static $regexp_by_example = array();
    if (!isset($regexp_by_example[$example_string])) {
      foreach ($regexp_available as $regexp) {
        if (implode(' ', preg_split(
            $regexp,
            str_replace(' ', '', $example_string),
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
          )) == $example_string) {
          break;
        }
      }
      if (!isset($regexp)) {
        throw new \Exception("Invalid example string '$example_string'.");
      }
      $regexp_by_example[$example_string] = $regexp;
    }
    $array = preg_split(
      $regexp_by_example[$example_string],
      $string,
      -1,
      PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
    );
    if ($lowercase) {
      $array = array_map('strtolower', $array);
    }
    return (FALSE !== $glue) ? implode($glue, $array) : $array;
  }
}
