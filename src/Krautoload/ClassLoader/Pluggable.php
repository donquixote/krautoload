<?php

namespace Krautoload;

class ClassLoader_Pluggable extends ClassLoader_Abstract implements ClassLoader_Pluggable_Interface {

  /**
   * Array of classes mapped to files.
   *
   * @var array
   */
  protected $classMap = array();

  /**
   * Nested array, where
   * - the top-level keys are logical base paths obtained from base namespaces,
   *   each with trailing directory separator.
   * - the second-level keys are physical base directories,
   *   each with trailing directory separator.
   * - the second-level values are NamespacePathPlugin_Interface objects.
   *
   * @var array
   */
  protected $namespaceMap = array();

  /**
   * Nested array, where
   * - the top-level keys are logical base paths obtained from base prefixes,
   *   each with trailing directory separator.
   * - the second-level keys are physical base directories,
   *   each with trailing directory separator.
   * - the second-level values are PrefixPathPlugin_Interface objects.
   *
   * @var array
   */
  protected $prefixMap = array();

  /**
   * @inheritdoc
   */
  public function addClassMap(array $classMap, $override = TRUE) {
    if (empty($this->classMap)) {
      $this->classMap = $classMap;
    }
    elseif ($override) {
      $this->classMap = array_merge($classMap, $this->classMap);
    }
    else {
      $this->classMap = array_merge($this->classMap, $classMap);
    }
  }

  /**
   * @inheritdoc
   */
  public function addClassFile($class, $file, $override = TRUE) {
    if ($override || !isset($this->classMap[$class])) {
      $this->classMap[$class] = $file;
    }
  }

  /**
   * @inheritdoc
   */
  public function addNamespacePlugin($logicalBasePath, $baseDir, NamespacePathPlugin_Interface $plugin) {
    $this->namespaceMap[$logicalBasePath][$baseDir] = $plugin;
  }

  /**
   * @inheritdoc
   */
  public function addPrefixPlugin($logicalBasePath, $baseDir, PrefixPathPlugin_Interface $plugin) {
    $this->prefixMap[$logicalBasePath][$baseDir] = $plugin;
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
    if (isset($this->classMap[$class])) {
      require $this->classMap[$class];
      return TRUE;
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
   * @return bool|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  protected function mapLoadClass(array $map, $class, $logicalBasePath, $relativePath) {

    $path = $logicalBasePath . $relativePath;
    while (TRUE) {
      // Check any plugin registered for this fragment.
      if (!empty($map[$logicalBasePath])) {
        /**
         * @var NamespacePathPlugin_Interface|PrefixPathPlugin_Interface $plugin
         */
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
   * @return bool|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  public function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class) {

    // Discard initial namespace separator.
    if ('\\' === $class[0]) {
      $class = substr($class, 1);
    }

    // First check if the literal class name is registered.
    if (isset($this->classMap[$class])) {
      if ($api->claimFile($this->classMap[$class])) {
        return TRUE;
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
   * @return bool|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  protected function apiMapFindFile($api, $map, $logicalBasePath, $relativePath) {
    $logicalPath = $logicalBasePath . $relativePath;
    while (TRUE) {

      // Check any plugin registered for this fragment.
      if (!empty($map[$logicalBasePath])) {
        /**
         * @var NamespacePathPlugin_Interface|PrefixPathPlugin_Interface $plugin
         */
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
