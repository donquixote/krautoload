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
   * Register a filepath for an individual class.
   *
   * @param string $class
   *   The fully-qualified class name, e.g. My\Class.
   * @param string $file_path
   *   The path, e.g. "../lib/My/Class.php".
   */
  public function addClassFile($class, $file_path);

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
