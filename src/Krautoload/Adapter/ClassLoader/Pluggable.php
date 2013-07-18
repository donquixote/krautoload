<?php

namespace Krautoload;

class Adapter_ClassLoader_Pluggable implements Adapter_ClassLoader_Interface {

  /**
   * @var ClassLoader_Pluggable_Interface
   */
  protected $finder;

  /**
   * @var array
   */
  protected $plugins = array();

  /**
   * Construct an empty loader, and wrap it into a new adapter.
   *
   * @return self
   */
  static function start() {
    $loader = new ClassLoader_Pluggable();
    return new self($loader);
  }

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
   * @inheritdoc
   */
  function getFinder() {
    return $this->finder;
  }

  /**
   * @inheritdoc
   */
  function krautoloadCallback($callback) {
    call_user_func($callback, $this);
  }

  /**
   * @inheritdoc
   */
  function krautoloadFile($file) {
    $callback = require $file;
    call_user_func($callback, $this);
  }

  /**
   * @inheritdoc
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
   * @inheritdoc
   */
  function addPrefixesPSR0(array $prefixes) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->addPrefixPSR0($prefix, $rootDirs);
    }
  }

  /**
   * @inheritdoc
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

  /**
   * @inheritdoc
   */
  function addPrefixes(array $prefixes) {
    $this->addPrefixesPSR0($prefixes);
  }

  /**
   * @inheritdoc
   */
  function addPrefix($prefix, $rootDirs) {
    $this->addPrefixPSR0($prefix, $rootDirs);
  }

  /**
   * @inheritdoc
   */
  function addNamespacesPSR0(array $namespaces) {
    foreach ($namespaces as $namespace => $rootDirs) {
      $this->addNamespacePSR0($namespace, $rootDirs);
    }
  }

  /**
   * @inheritdoc
   */
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

  /**
   * @inheritdoc
   */
  function addNamespacesShallowPSR0(array $namespaces) {
    foreach ($namespaces as $namespace => $baseDirs) {
      $this->addNamespaceShallowPSR0($namespace, $baseDirs);
    }
  }

  /**
   * @inheritdoc
   */
  function addNamespaceShallowPSR0($namespace, $baseDirs) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $this->plugins['ShallowPSR0']);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefixesPEAR(array $prefixes, $preventCollision = FALSE) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->addPrefixPEAR($prefix, $rootDirs, $preventCollision);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefixPEAR($prefix, $rootDirs, $preventCollision = FALSE) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $plugin = $preventCollision ? $this->plugins['ShallowPEAR_Uncertain'] : $this->plugins['ShallowPEAR'];
    foreach ((array) $rootDirs as $rootDir) {
      $baseDir = strlen($rootDir) ? $rootDir . DIRECTORY_SEPARATOR : '';
      $baseDir .= $logicalBasePath;
      $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefixesShallowPEAR(array $prefixes, $preventCollision = FALSE) {
    foreach ($prefixes as $prefix => $baseDirs) {
      $this->addPrefixPEAR($prefix, $baseDirs, $preventCollision);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefixShallowPEAR($prefix, $baseDirs, $preventCollision = FALSE) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $plugin = $preventCollision ? $this->plugins['ShallowPEAR_Uncertain'] : $this->plugins['ShallowPEAR'];
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
    }
  }

  /**
   * @inheritdoc
   */
  function addNamespacesPSRX(array $namespaces) {
    foreach ($namespaces as $namespace => $baseDirs) {
      $this->addNamespacePSRX($namespace, $baseDirs);
    }
  }

  /**
   * @inheritdoc
   */
  function addNamespacePSRX($namespace, $baseDirs) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    foreach ((array) $baseDirs as $baseDir) {
      $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
      $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $this->plugins['PSRX']);
    }
  }

  /**
   * @inheritdoc
   */
  function addNamespacePlugin($namespace, $baseDir, $plugin) {
    $logicalBasePath = $this->namespaceLogicalPath($namespace);
    $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
    $this->finder->addNamespacePlugin($logicalBasePath, $baseDir, $plugin);

  }

  /**
   * @inheritdoc
   */
  function addPrefixPlugin($prefix, $baseDir, $plugin) {
    $logicalBasePath = $this->prefixLogicalPath($prefix);
    $baseDir = strlen($baseDir) ? $baseDir . DIRECTORY_SEPARATOR : '';
    $this->finder->addPrefixPlugin($logicalBasePath, $baseDir, $plugin);
  }

  /**
   * @inheritdoc
   */
  function addClassMap(array $classMap, $override = FALSE) {
    $this->finder->addClassMap($classMap, $override);
  }

  /**
   * @inheritdoc
   */
  function addClassFile($class, $file, $override = TRUE) {
    $this->finder->addClassFile($class, $file, $override);
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
