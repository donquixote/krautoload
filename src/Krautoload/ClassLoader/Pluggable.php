<?php

namespace Krautoload;

class ClassLoader_Pluggable extends ClassLoader_Abstract implements ClassLoader_Pluggable_Interface {

  protected $classes = array();
  protected $namespaceMap = array();
  protected $prefixMap = array();

  /**
   * Register a filepath for an individual class.
   *
   * @param string $class
   *   The class, e.g. My_Class
   * @param string $file_path
   *   The path, e.g. "../lib/My/Class.php".
   */
  public function registerClass($class, $file_path) {
    $this->classes[$class][$file_path] = TRUE;
  }

  /**
   * Register a plugin for a namespace.
   *
   * @param string $namespace
   *   The namespace, e.g. "My\Library"
   * @param string $dir
   *   The deep path, e.g. "../lib/My/Namespace"
   * @param FinderPlugin_Interface $plugin
   *   The plugin.
   */
  public function registerNamespacePathPlugin($logicalPath, $dir, $plugin) {
    $this->namespaceMap[$logicalPath][$dir] = $plugin;
  }

  /**
   * Register a plugin for a prefix.
   *
   * @param string $prefix
   *   The prefix, e.g. "My_Library"
   * @param string $dir
   *   The deep filesystem location, e.g. "../lib/My/Prefix".
   * @param FinderPlugin_Interface $plugin
   *   The plugin. See 
   */
  public function registerPrefixPathPlugin($prefix_path_fragment, $dir, $plugin) {
    $this->prefixMap[$prefix_path_fragment][$dir] = $plugin;
  }

  /**
   * Callback for class loading. This will include ("require") the file found.
   *
   * @param string $class
   *   The class to load.
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
            if (class_exists($class, FALSE) || interface_exists($class, FALSE)) {
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
  protected function mapLoadClass($map, $class, $logicalBasePath, $relativePath) {

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
}
