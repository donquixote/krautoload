<?php

namespace Krautoload;

class ClassLoader_Pluggable extends ClassLoader_Abstract implements ClassLoader_Pluggable_Interface {

  protected $classes = array();
  protected $namespaceMap = array();
  protected $prefixMap = array();

  /**
   * @inheritdoc
   */
  public function addClassFile($class, $file_path) {
    $this->classes[$class][$file_path] = TRUE;
  }

  /**
   * @inheritdoc
   */
  public function addNamespacePlugin($logicalPath, $dir, NamespacePathPlugin_Interface $plugin) {
    $this->namespaceMap[$logicalPath][$dir] = $plugin;
  }

  /**
   * @inheritdoc
   */
  public function addPrefixPlugin($logicalBasePath, $dir, PrefixPathPlugin_Interface $plugin) {
    $this->prefixMap[$logicalBasePath][$dir] = $plugin;
  }

  /**
   * @inheritdoc
   */
  function loadClass($class) {

    // Discard initial namespace separator.
    if ('\\' === $class[0]) {
      $class = substr($class, 1);
    }

    // First check if the literal class name is registered.
    if (!empty($this->classes[$class])) {
      foreach ($this->classes[$class] as $file => $skipClassExists) {
        if (is_file($file)) {
          if ($skipClassExists) {
            // Assume that the file does indeed define the class.
            include $file;
            return TRUE;
          }
          else {
            // Assume that the file MAY define the class.
            include_once $file;
            if (class_exists($class, FALSE) || interface_exists($class, FALSE) || trait_exists($class, FALSE)) {
              return TRUE;
            }
          }
        }
      }
    }

    // Distinguish namespace vs underscore-only.
    // This is an internal implementation choice, and has nothing to do with
    // whether or not the PSR-0 spec is correctly implemented.
    if (FALSE !== $pos = strrpos($class, '\\')) {

      // Loop through positions of '\\', backwards.
      $logicalBasePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $relativePath = substr($class, $pos + 1) . '.php';
      if ($this->mapLoadClass($this->namespaceMap, $class, $logicalBasePath, $relativePath)) {
        return TRUE;
      }
    }
    else {

      // The class is not within a namespace.
      // Fall back to the prefix-based finder.
      $logicalBasePath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
      if ($this->mapLoadClass($this->prefixMap, $class, $logicalBasePath, '')) {
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
   * @param string $class
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
  protected function mapLoadClass(array $map, $class, $logicalBasePath, $relativePath) {

    $path = $logicalBasePath . $relativePath;
    while (TRUE) {
      // Check any plugin registered for this fragment.
      if (!empty($map[$logicalBasePath])) {
        foreach ($map[$logicalBasePath] as $baseDir => $plugin) {
          if ($plugin->pluginLoadClass($class, $baseDir, $relativePath)) {
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
        $relativePath = $path;
      }
      elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
        $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
        $relativePath = substr($path, $pos + 1);
      }
      else {
        $logicalBasePath = '';
        $relativePath = $path;
      }
    }
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
