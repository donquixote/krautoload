<?php

namespace Krautoload;

class RegistrationHub {

  /**
   * @var ClassLoader_Pluggable_Interface
   */
  protected $finder;

  /**
   * @var array
   */
  protected $plugins = array();

  /**
   * @param ClassLoader_Pluggable_Interface $finder
   *   A finder object where namespace and prefix plugins can be registered.
   */
  function __construct(ClassLoader_Pluggable_Interface $finder) {
    $this->finder = $finder;
    $this->plugins['ShallowPEAR'] = new PrefixPathPlugin_ShallowPEAR();
    $this->plugins['ShallowPEAR_Uncertain'] = new PrefixPathPlugin_ShallowPEAR_Uncertain();
    $this->plugins['ShallowPSR0'] = new NamespacePathPlugin_ShallowPSR0();
    $this->plugins['PSRX'] = new NamespacePathPlugin_PSRX();
  }

  /**
   * @return ClassLoader_Pluggable_Interface
   */
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
      $this->addClassMap($class_map, FALSE);
    }
  }

  /**
   * Registers Composer-style PSR-0 prefixes.
   * These prefixes can apply to both namespaced and non-namespaced classes.
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
   * Registers a Composer-style PSR-0 prefix.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $rootDirs
   *   The location(s) of the classes
   */
  function addPrefixPSR0($prefix, $rootDirs) {

    if ('' === $prefix) {
      // We consider this as a "fallback".
      $this->addNamespacePSR0('', $rootDirs);
      $this->addPrefixPEAR('', $rootDirs, TRUE);
    }
    elseif ('\\' === substr($prefix, -1)) {
      // We know that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $this->addNamespacePSR0(substr($prefix, 0, -1), $rootDirs);
    }
    elseif (FALSE !== strrpos($prefix, '\\')) {
      // We assume that $prefix is meant as a namespace,
      // and the paths are PSR-0 directories.
      $namespace = $prefix;
      $this->addNamespacePSR0($namespace, $rootDirs);
      foreach ((array) $rootDirs as $rootDir) {
        $this->addClassFile($prefix, $rootDir . '.php');
      }
      // @todo
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    elseif ('_' === substr($prefix, -1)) {
      // We assume that $prefix is meant as a PEAR prefix,
      // and the paths are PSR-0 directories.
      $this->addPrefixPEAR(substr($prefix, 0, -1), $rootDirs, TRUE);
      // @todo
      //   Register special plugins to cover other FQCNs
      //   that happen to begin with with the prefix.
    }
    else {
      // We assume that $prefix is meant as a PEAR prefix OR as namespace,
      // and the paths are PSR-0 or PEAR directories.
      $this->addNamespacePSR0($prefix, $rootDirs);
      $this->addPrefixPEAR($prefix, $rootDirs, TRUE);
      foreach ((array) $rootDirs as $rootDir) {
        $this->addClassFile($prefix, $rootDir . '.php');
      }
      // @todo
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
        $this->finder->addNamespacePlugin('', $rootDir, $this->plugins['ShallowPSR0']);
        $this->finder->addPrefixPlugin('', $rootDir, $this->plugins['ShallowPEAR']);
      }
    }
    else {
      $logicalBasePath = $this->namespaceLogicalPath($namespace);
      foreach ((array) $rootDirs as $rootDir) {
        $baseDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
        $baseDir .= $logicalBasePath;
        $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPSR0']);
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
      $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPSR0']);
    }
  }

  function addPrefixesPEAR($prefixes, $preventCollision = FALSE) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->addPrefixPEAR($prefix, $rootDirs, $preventCollision);
    }
  }

  function addPrefixPEAR($prefix, $rootDirs, $preventCollision = FALSE) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $plugin = $preventCollision ? $this->plugins['ShallowPEAR_Uncertain'] : $this->plugins['ShallowPEAR'];
    foreach ((array) $rootDirs as $rootDir) {
      $baseDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
      $baseDir .= $logicalBasePath;
      $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
    }
  }

  function addPrefixesShallowPEAR($prefixes, $preventCollision = FALSE) {
    foreach ($prefixes as $prefix => $baseDirs) {
      $this->addPrefixPEAR($prefix, $baseDirs, $preventCollision);
    }
  }

  function addPrefixShallowPEAR($prefix, $baseDirs, $preventCollision = FALSE) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $plugin = $preventCollision ? $this->plugins['ShallowPEAR_Uncertain'] : $this->plugins['ShallowPEAR'];
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
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
      $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $this->plugins['PSRX']);
    }
  }

  /**
   * @param string $namespace
   * @param string $baseDir
   * @param NamespacePathPlugin_Interface $plugin
   */
  function addNamespacePlugin($namespace, $baseDir, $plugin) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
    $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $plugin);

  }

  /**
   * @param string $prefix
   * @param string $baseDir
   * @param PrefixPathPlugin_Interface $plugin
   */
  function addPrefixPlugin($prefix, $baseDir, $plugin) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
    $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
  }

  /**
   * @param array $classMap
   *   An array where the keys are classes, and the values are filenames.
   * @param bool $override
   */
  function addClassMap(array $classMap, $override = FALSE) {
    $this->finder->addClassMap($classMap, $override);
  }

  /**
   * @param string $class
   * @param string $file
   * @param bool $override
   */
  function addClassFile($class, $file, $override = TRUE) {
    $this->finder->addClassFile($class, $file, $override);
  }

  /**
   * @param array $namespaces
   * @return SearchableNamespaces_Interface
   * @throws \Exception
   */
  function buildSearchableNamespaces($namespaces = array()) {
    if (!$this->finder instanceof NamespaceInspector_Interface) {
      throw new \Exception("Introspection not possible with the given class loader object.");
    }
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
