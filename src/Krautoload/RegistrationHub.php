<?php

namespace Krautoload;

class RegistrationHub {

  protected $finder;
  protected $plugins = array();

  /**
   * @param \Krautoload\ClassLoader_Pluggable_Interface $finder
   *   A finder object where namespace and prefix plugins can be registered.
   */
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
      $this->composerPrefixes($namespaces);
    }
    if (is_file($dir . '/composer/autoload_classmap.php')) {
      $class_map = include $dir . '/composer/autoload_classmap.php';
      foreach ($class_map as $class => $file) {
        $this->finder->registerClass($class, $file);
      }
    }
  }

  /**
   * Adds prefixes.
   *
   * @param array $prefixes
   *   Prefixes to add
   */
  function composerPrefixes(array $prefixes) {
    foreach ($prefixes as $prefix => $path) {
      $this->composerPrefix($prefix, $path);
    }
  }

  /**
   * Registers a set of classes
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $paths
   *   The location(s) of the classes
   */
  function composerPrefix($prefix, $paths) {

    if (!$prefix) {
      // We consider this as a "fallback".
    }
    elseif ('\\' === substr($prefix, -1)) {
      // We assume that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $namespace = substr($prefix, 0, -1);
      foreach ((array) $paths as $path) {
        $this->namespacePSR0($namespace, $path);
      }
    }
    elseif (FALSE !== strrpos($prefix, '\\')) {
      // We assume that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $namespace = $prefix;
      foreach ((array) $paths as $path) {
        $this->namespacePSR0($namespace, $path);
        $this->classFile($prefix, $path . '.php');
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    elseif ('_' === substr($prefix, -1)) {
      // We assume that $prefix is meant as a PEAR prefix,
      // and the paths are PSR-0 directories.
      foreach ((array) $paths as $path) {
        $this->prefixPEAR(substr($prefix, 0, -1), $path);
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    else {
      // We assume that $prefix is meant as a PEAR prefix OR as namespace,
      // and the paths are PSR-0 or PEAR directories.
      foreach ((array) $paths as $path) {
        $this->namespacePSR0($prefix, $path);
        $this->prefixPEAR($prefix, $path);
        $this->classFile($prefix, $path . '.php');
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
  }

  function namespacesPSR0($namespaces, $plugin = NULL) {
    foreach ($namespaces as $namespace => $paths) {
      foreach ((array) $paths as $path) {
        $this->namespacePSR0($namespace, $path);
      }
    }
  }

  function namespacesPluginPSR0($namespaces, $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->plugins['ShallowPSR0'];
    }
    elseif (is_string($plugin)) {
      $class = "Krautoload\\FinderPlugin_ShallowPSR0_$plugin";
      $plugin = new $class();
    }
    foreach ($namespaces as $namespace => $paths) {
      foreach ((array) $paths as $path) {
        $this->namespacePluginPSR0($namespace, $path, $plugin);
      }
    }
  }

  function namespacePSR0($namespace, $root_path) {
    $namespace_path_fragment = $this->namespacePathFragment($namespace);
    $deep_path = strlen($root_path) ? $root_path . DIRECTORY_SEPARATOR : '';
    $deep_path .= $namespace_path_fragment;
    $this->finder->registerNamespacePathPlugin($namespace_path_fragment, $deep_path, $this->plugins['ShallowPSR0']);
  }

  function namespacePluginPSR0($namespace, $root_path, $plugin) {
    $namespace_path_fragment = $this->namespacePathFragment($namespace);
    $deep_path = strlen($root_path) ? $root_path . DIRECTORY_SEPARATOR : '';
    $deep_path .= $namespace_path_fragment;
    $this->finder->registerNamespacePathPlugin($namespace_path_fragment, $deep_path, $plugin);
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

  function namespacesPSRX($namespaces) {
    foreach ($namespaces as $namespace => $paths) {
      foreach ((array) $paths as $path) {
        $this->namespacePSRX($namespace, $path);
      }
    }
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
