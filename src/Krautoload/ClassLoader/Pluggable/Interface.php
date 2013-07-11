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
interface ClassLoader_Pluggable_Interface extends ClassLoader_Interface {

  /**
   * Register an array that maps classes to files.
   * The loader will assume for each of the given files that this file does
   * exist, and that its inclusion makes the expected class available.
   * There will be no class_exists() or file_exists() checks, and there will be
   * no require_once or include_once.
   *
   * @param array $classMap
   *   An array where the keys are classes, the values are file paths.
   * @param bool $override
   *   Whether to override previously added classes.
   */
  public function addClassMap(array $classMap, $override = TRUE);

  /**
   * Register a filepath for an individual class.
   * The loader will assume that the given file does exist, and that its
   * inclusion makes the expected class available.
   * There will be no class_exists() or file_exists() checks, and there will be
   * no require_once or include_once.
   *
   * @param string $class
   *   The fully-qualified class name, e.g. My\Class.
   * @param string $file
   *   The path, e.g. "../lib/My/Class.php".
   * @param bool $override
   *   Whether to override previously added classes.
   */
  public function addClassFile($class, $file, $override = TRUE);

  /**
   * Register a plugin for a namespace and path.
   *
   * @param string $logicalBasePath
   *   The logical base path determined from the namespace,
   *   by replacing each namespace separator with a directory separator.
   * @param string $baseDir
   *   The base dir associated with the namespace.
   * @param NamespacePathPlugin_Interface $plugin
   *   The plugin that handles class loader lookups under this namespace.
   */
  public function addNamespacePlugin($logicalBasePath, $baseDir, NamespacePathPlugin_Interface $plugin);

  /**
   * Register a plugin for a prefix.
   *
   * @param string $logicalBasePath
   *   The logical base path obtained from the prefix,
   *   by replacing each underscore with a directory separator.
   * @param string $baseDir
   *   The (deep) base directory associated with the prefix.
   * @param PrefixPathPlugin_Interface $plugin
   *   The plugin that handles class loader lookups under this namespace.
   */
  public function addPrefixPlugin($logicalBasePath, $baseDir, PrefixPathPlugin_Interface $plugin);
}
