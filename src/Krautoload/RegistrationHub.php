<?php

namespace Krautoload;

class RegistrationHub {

  protected $finder;
  protected $plugins = array();

  function __construct($finder) {
    $this->finder = $finder;
    $this->plugins['ShallowPEAR'] = new FinderPlugin_ShallowPEAR();
    $this->plugins['ShallowPSR0'] = new FinderPlugin_ShallowPSR0();
    $this->plugins['PSRX'] = new FinderPlugin_PSRX();
  }

  /**
   * @param callback $callback
   *   Registration callback, which takes as an argument the registration hub.
   */
  function krautoloadCallback($callback) {
    call_user_func($callback, $this);
  }

  /**
   * @param string $file
   *   Path to a PHP file that, on inclusion, returns a registration callback.
   */
  function krautoloadFile($file) {
    $callback = require $file;
    call_user_func($callback, $this);
  }

  /**
   * @param string $dir
   *   Vendor directory of a project using composer.
   *   This allows to use Krautoload for composer-based PHP projects.
   */
  function composerVendorDir($dir) {
    if (is_file($dir . '/composer/autoload_namespaces.php')) {
      $namespaces = include $dir . '/composer/autoload_namespaces.php';
      foreach ($namespaces as $namespace => $root_path) {
        $this->namespacePSR0($namespace, $root_path);
      }
    }
    if (is_file($dir . '/composer/autoload_classmap.php')) {
      $class_map = include $dir . '/composer/autoload_classmap.php';
      foreach ($class_map as $class => $file) {
        $this->finder->registerClass($class, $file);
      }
    }
  }

  function namespacePSR0($namespace, $root_path) {
    $namespace_path_fragment = $this->namespacePathFragment($namespace);
    $deep_path = strlen($root_path) ? $root_path . DIRECTORY_SEPARATOR : '';
    $deep_path .= $namespace_path_fragment;
    $this->finder->registerNamespacePathPlugin($namespace_path_fragment, $deep_path, $this->plugins['ShallowPSR0']);
  }

  function namespaceShallowPSR0($namespace, $deep_path) {
    $namespace_path_fragment = $this->namespacePathFragment($namespace);
    $deep_path = strlen($deep_path) ? $deep_path . DIRECTORY_SEPARATOR : '';
    $this->finder->registerNamespacePathPlugin($namespace_path_fragment, $deep_path, $this->plugins['ShallowPSR0']);
  }

  function prefixPEAR($prefix, $root_path) {
    $prefix_path_fragment = $this->prefixPathFragment($prefix);
    $deep_path = strlen($root_path) ? $root_path . DIRECTORY_SEPARATOR : '';
    $deep_path .= $prefix_path_fragment;
    $this->finder->registerPrefixPathPlugin($prefix_path_fragment, $deep_path, $this->plugins['ShallowPEAR']);
  }

  function prefixShallowPEAR($prefix, $deep_path) {
    $prefix_path_fragment = $this->prefixPathFragment($prefix);
    $deep_path = strlen($deep_path) ? $deep_path . DIRECTORY_SEPARATOR : '';
    $this->finder->registerPrefixPathPlugin($prefix_path_fragment, $deep_path, $this->plugins['ShallowPEAR']);
  }

  function namespacePSRX($namespace, $deep_path) {
    $namespace_path_fragment = $this->namespacePathFragment($namespace);
    $deep_path = strlen($deep_path) ? $deep_path . DIRECTORY_SEPARATOR : '';
    $this->finder->registerNamespacePathPlugin($namespace_path_fragment, $deep_path, $this->plugins['PSRX']);
  }

  function classFile($class, $file) {
    $this->finder->registerClass($class, $file);
  }

  /**
   * Replace the namespace separator with directory separator.
   *
   * @param string $namespace
   *   Namespace without trailing namespace separator.
   *
   * @return string
   *   Path fragment representing the namespace, with trailing DIRECTORY_SEPARATOR.
   */
  protected function namespacePathFragment($namespace) {
    return
      strlen($namespace)
      ? str_replace('\\', DIRECTORY_SEPARATOR, $namespace . '\\')
      : ''
    ;
  }

  /**
   * Convert the underscores of a prefix into directory separators.
   *
   * @param string $prefix
   *   Prefix, without trailing underscore.
   *
   * @return string
   *   Path fragment representing the prefix, with trailing DIRECTORY_SEPARATOR.
   */
  protected function prefixPathFragment($prefix) {
    return
      strlen($prefix)
      ? str_replace('_', DIRECTORY_SEPARATOR, $prefix . '_')
      : ''
    ;
  }
}
