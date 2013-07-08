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
    $this->plugins['ShallowPEAR'] = new PrefixPathPlugin_ShallowPEAR();
    $this->plugins['ShallowPSR0'] = new NamespacePathPlugin_ShallowPSR0();
    $this->plugins['PSRX'] = new NamespacePathPlugin_PSRX();
  }

  function getFinder() {
    return $this->finder;
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
      $this->addPrefixesPSR0($namespaces);
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
  function addPrefixesPSR0(array $prefixes) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->addPrefixPSR0($prefix, $rootDirs);
    }
  }

  /**
   * Registers a set of classes
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $rootDirs
   *   The location(s) of the classes
   */
  function addPrefixPSR0($prefix, $rootDirs) {

    if (!$prefix) {
      // We consider this as a "fallback".
    }
    elseif ('\\' === substr($prefix, -1)) {
      // We assume that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $namespace = substr($prefix, 0, -1);
      foreach ((array) $rootDirs as $rootDir) {
        $this->addNamespacePSR0($namespace, $rootDir);
      }
    }
    elseif (FALSE !== strrpos($prefix, '\\')) {
      // We assume that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $namespace = $prefix;
      foreach ((array) $rootDirs as $rootDir) {
        $this->addNamespacePSR0($namespace, $rootDir);
        $this->addClassFile($prefix, $rootDir . '.php');
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    elseif ('_' === substr($prefix, -1)) {
      // We assume that $prefix is meant as a PEAR prefix,
      // and the paths are PSR-0 directories.
      foreach ((array) $rootDirs as $rootDir) {
        $this->addPrefixPEAR(substr($prefix, 0, -1), $rootDir);
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    else {
      // We assume that $prefix is meant as a PEAR prefix OR as namespace,
      // and the paths are PSR-0 or PEAR directories.
      foreach ((array) $rootDirs as $rootDir) {
        $this->addNamespacePSR0($prefix, $rootDir);
        $this->addPrefixPEAR($prefix, $rootDir);
        $this->addClassFile($prefix, $rootDir . '.php');
      }
      // TODO:
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
  }

  function addNamespacesPSR0($namespaces) {
    foreach ($namespaces as $namespace => $rootDirs) {
      $this->addNamespacePSR0($namespace, $rootDirs);
    }
  }

  function addNamespacePSR0($namespace, $rootDirs) {
    if (empty($namespace)) {
      foreach ((array) $rootDirs as $rootDir) {
        $rootDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
        $this->finder->registerNamespacePathPlugin('', $rootDir, $this->plugins['ShallowPSR0']);
        $this->finder->registerPrefixPathPlugin('', $rootDir, $this->plugins['ShallowPEAR']);
      }
    }
    else {
      $logicalBasePath = $this->namespaceLogicalPath($namespace);
      foreach ((array) $rootDirs as $rootDir) {
        $baseDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
        $baseDir .= $logicalBasePath;
        $this->finder->registerNamespacePathPlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPSR0']);
      }
    }
  }

  function addNamespacesShallowPSR0($namespaces) {
    foreach ($namespaces as $namespace => $baseDirs) {
      $this->addNamespaceShallowPSR0($namespace, $baseDirs);
    }
  }

  function addNamespaceShallowPSR0($namespace, $baseDirs) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->registerNamespacePathPlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPSR0']);
    }
  }

  function addPrefixesPEAR($prefixes) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->addPrefixPEAR($prefix, $rootDirs);
    }
  }

  function addPrefixPEAR($prefix, $rootDirs) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    foreach ((array) $rootDirs as $rootDir) {
      $baseDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
      $baseDir .= $logicalBasePath;
      $this->finder->registerPrefixPathPlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPEAR']);
    }
  }

  function addPrefixesShallowPEAR($prefixes) {
    foreach ($prefixes as $prefix => $baseDirs) {
      $this->addPrefixPEAR($prefix, $baseDirs);
    }
  }

  function addPrefixShallowPEAR($prefix, $baseDirs) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->registerPrefixPathPlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPEAR']);
    }
  }

  function addNamespacesPSRX($namespaces) {
    foreach ($namespaces as $namespace => $baseDirs) {
      $this->addNamespacePSRX($namespace, $baseDirs);
    }
  }

  function addNamespacePSRX($namespace, $baseDirs) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->registerNamespacePathPlugin($logicalBasePath, $baseDir, $this->plugins['PSRX']);
    }
  }

  function addClassFile($class, $file) {
    $this->finder->registerClass($class, $file);
  }

  function buildSearchableNamespaces($namespaces = array()) {
    $searchable = new SearchableNamespaces_Default($this->finder);
    $searchable->addNamespaces($namespaces);
    return $searchable;
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
  protected function namespaceLogicalPath($namespace) {
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
  protected function prefixLogicalPath($prefix) {
    return
      strlen($prefix)
      ? str_replace('_', DIRECTORY_SEPARATOR, $prefix . '_')
      : ''
    ;
  }
}
