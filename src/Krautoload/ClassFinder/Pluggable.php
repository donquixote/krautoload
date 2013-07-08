<?php

namespace Krautoload;

/**
 * This thing can find files for classes, but it uses a funky signature, which
 * makes it different from other class finders you may have seen elsewhere.
 *
 * The main class finding method is apiFindFile(), which does not return a file
 * or include it directly, but instead sends the result to the $api object
 * passed into the method as a parameter.
 *
 * The benefit is that all filesystem contact can be mocked out, by passing in
 * a different implementation for the $api argument.
 */
class ClassFinder_Pluggable extends ClassLoader_Pluggable implements ClassFinder_Interface {

  /**
   * Alternative to loadClass() that passes an $api argument around.
   *
   * You normally don't want to call this directly, it is rather meant as a
   * proof-of-concept implementation.
   */
  public function apiLoadClass($class) {
    $api = new InjectedAPI_ClassFinder_LoadClass($class);
    // $api has a ->suggestFile($file) method, which returns TRUE if the
    // suggested file exists.
    // The ->apiFindFile() method is supposed to suggest a number of files
    // to the $api, until one is successful, and then return TRUE. Or return
    // FALSE, if nothing was found.
    if ($this->apiFindFile($api, $class)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param InjectedAPI_ClassFinder_Interface $api
   *   API object with a suggestFile() method.
   *   We are supposed to call $api->suggestFile($file) with all suggestions we
   *   can find, until it returns TRUE. Once suggestFile() returns TRUE, we stop
   *   and return TRUE as well. The $file will be in the $api object, so we
   *   don't need to return it.
   * @param string $class
   *   The name of the class, with all namespaces prepended.
   *   E.g. Some\Namespace\Some\Class
   *
   * @return TRUE|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  public function apiFindFile($api, $class) {

    // Discard initial namespace separator.
    if ('\\' === $class[0]) {
      $class = substr($class, 1);
    }

    // First check if the literal class name is registered.
    if (!empty($this->classes[$class])) {
      foreach ($this->classes[$class] as $file => $skip_class_exists) {
        if ($skip_class_exists) {
          if ($api->guessFile($file)) {
            return TRUE;
          }
        }
        else {
          if ($api->guessFileCandidate($file)) {
            return TRUE;
          }
        }
      }
    }

    // Distinguish namespace vs underscore-only.
    if (FALSE !== $pos = strrpos($class, '\\')) {

      // Loop through positions of '\\', backwards.
      $logicalBasePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $relativePath = substr($class, $pos + 1) . '.php';
      if ($this->apiMapFindFile($api, $this->namespaceMap, $logicalBasePath, $relativePath)) {
        return TRUE;
      }
    }
    else {

      // The class is not within a namespace.
      // Fall back to the prefix-based finder.
      $logicalBasePath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
      if ($this->apiMapFindFile($api, $this->prefixMap, $logicalBasePath, '')) {
        return TRUE;
      }
    }
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $logicalBasePath . $relativePath
   *
   * @param array $map
   *   Either the namespace map or the prefix 
   * @param InjectedAPI_ClassFinder_Interface $api
   *   API object with a suggestFile() method.
   *   We are supposed to call $api->suggestFile($file) with all suggestions we
   *   can find, until it returns TRUE. Once suggestFile() returns TRUE, we stop
   *   and return TRUE as well. The $file will be in the $api object, so we
   *   don't need to return it.
   * @param string $logicalBasePath
   *   First part of the canonical path, with trailing DIRECTORY_SEPARATOR.
   * @param string $relativePath
   *   Second part of the canonical path, ending with '.php'.
   *
   * @return TRUE|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  protected function apiMapFindFile($api, $map, $logicalBasePath, $relativePath) {
    $logicalPath = $logicalBasePath . $relativePath;
    while (TRUE) {

      // Check any plugin registered for this fragment.
      if (!empty($map[$logicalBasePath])) {
        foreach ($map[$logicalBasePath] as $baseDir => $plugin) {
          if ($plugin->pluginFindFile($api, $baseDir, $relativePath)) {
            return TRUE;
          }
        }
      }

      // Continue with parent fragment.
      if ('' === $logicalBasePath) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $logicalBasePath) {
        // This happens if a class begins with an underscore.
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
      elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
        $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
        $relativePath = substr($logicalPath, $pos + 1);
      }
      else {
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
    }
  }
}
