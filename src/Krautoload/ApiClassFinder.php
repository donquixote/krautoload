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
class ApiClassFinder {

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
  public function registerNamespacePathPlugin($namespace_path_fragment, $dir, $plugin) {
    $this->namespaceMap[$namespace_path_fragment][$dir] = $plugin;
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
   * Finds the path to the file where the class is defined.
   *
   * @param InjectedAPI $api
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
      foreach ($this->classes[$class] as $filepath => $true) {
        if ($api->guessFileCandidate($filepath)) {
          return TRUE;
        }
      }
    }

    // Distinguish namespace vs underscore-only.
    if (FALSE !== $pos = strrpos($class, '\\')) {

      // Loop through positions of '\\', backwards.
      $namespace_path_fragment = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      $path_suffix = substr($class, $pos + 1) . '.php';
      if ($this->mapFindFile($this->namespaceMap, $api, $namespace_path_fragment, $path_suffix)) {
        return TRUE;
      }
    }
    else {

      // The class is not within a namespace.
      // Fall back to the prefix-based finder.
      $prefix_path_fragment = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
      if ($this->mapFindFile($this->prefixMap, $api, $prefix_path_fragment, '')) {
        return TRUE;
      }
    }
  }

  /**
   * Find the file for a class that in PSR-0 or PEAR would be in
   * $psr_0_root . '/' . $path_fragment . $path_suffix
   *
   * @param array $map
   *   Either the namespace map or the prefix
   * @param InjectedAPI $api
   *   API object with a suggestFile() method.
   *   We are supposed to call $api->suggestFile($file) with all suggestions we
   *   can find, until it returns TRUE. Once suggestFile() returns TRUE, we stop
   *   and return TRUE as well. The $file will be in the $api object, so we
   *   don't need to return it.
   * @param string $path_fragment
   *   First part of the canonical path, with trailing DIRECTORY_SEPARATOR.
   * @param string $path_suffix
   *   Second part of the canonical path, ending with '.php'.
   *
   * @return TRUE|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  protected function mapFindFile($map, $api, $path_fragment, $path_suffix) {
    $path = $path_fragment . $path_suffix;
    while (TRUE) {

      // Check any plugin registered for this fragment.
      if (!empty($map[$path_fragment])) {
        foreach ($map[$path_fragment] as $dir => $plugin) {
          if ($plugin->pluginFindFile($api, $path_fragment, $dir, $path_suffix)) {
            return TRUE;
          }
        }
      }

      // Continue with parent fragment.
      if ('' === $path_fragment) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $path_fragment) {
        // This happens if a class begins with an underscore.
        $path_fragment = '';
        $path_suffix = $path;
      }
      elseif (FALSE !== $pos = strrpos($path_fragment, DIRECTORY_SEPARATOR, -2)) {
        $path_fragment = substr($path_fragment, 0, $pos + 1);
        $path_suffix = substr($path, $pos + 1);
      }
      else {
        $path_fragment = '';
        $path_suffix = $path;
      }
    }
  }
}
