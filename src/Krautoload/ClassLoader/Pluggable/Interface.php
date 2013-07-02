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
interface ClassLoader_Pluggable_Interface {

  /**
   * Register a filepath for an individual class.
   *
   * @param string $class
   *   The class, e.g. My_Class
   * @param string $file_path
   *   The path, e.g. "../lib/My/Class.php".
   */
  public function registerClass($class, $file_path);

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
  public function registerNamespacePathPlugin($namespace_path_fragment, $dir, $plugin);

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
  public function registerPrefixPathPlugin($prefix_path_fragment, $dir, $plugin);
}
