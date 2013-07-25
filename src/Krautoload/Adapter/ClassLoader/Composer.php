<?php

namespace Krautoload;

class Adapter_ClassLoader_Composer implements Adapter_ClassLoader_Interface {

  /**
   * @var ClassLoader_Composer_Interface
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
    $loader = new ClassLoader_Composer_Basic();
    return new self($loader);
  }

  /**
   * @param ClassLoader_Composer_Interface $finder
   *   A finder object where namespace and prefix plugins can be registered.
   */
  function __construct(ClassLoader_Composer_Interface $finder) {
    $this->finder = $finder;
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
      $this->finder->add($prefix, $rootDirs);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefixPSR0($prefix, $rootDirs) {
    $this->finder->add($prefix, $rootDirs);
  }

  /**
   * @inheritdoc
   */
  function addPrefixes(array $prefixes) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->finder->add($prefix, $rootDirs);
    }
  }

  /**
   * @inheritdoc
   */
  function addPrefix($prefix, $rootDirs) {
    $this->finder->add($prefix, $rootDirs);
  }

  /**
   * @inheritdoc
   */
  function addMultiple(array $prefixes) {
    foreach ($prefixes as $prefix => $rootDirs) {
      $this->finder->add($prefix, $rootDirs);
    }
  }

  /**
   * @inheritdoc
   */
  function add($prefix, $rootDirs) {
    $this->finder->add($prefix, $rootDirs);
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
    $namespace = trim($namespace, '\\');
    $namespace = strlen($namespace) ? $namespace . '\\' : '';
    $this->finder->add($namespace, $rootDirs);
  }

  /**
   * @inheritdoc
   */
  function addNamespacesShallowPSR0(array $namespaces) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addNamespaceShallowPSR0($namespace, $baseDirs) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
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
    $this->finder->add($prefix, $rootDirs);
  }

  /**
   * @inheritdoc
   */
  function addPrefixesShallowPEAR(array $prefixes, $preventCollision = FALSE) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addPrefixShallowPEAR($prefix, $baseDirs, $preventCollision = FALSE) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addNamespacesPSRX(array $namespaces) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addNamespacePSRX($namespace, $baseDirs) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addNamespacePlugin($namespace, $baseDir, $plugin) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
  }

  /**
   * @inheritdoc
   */
  function addPrefixPlugin($prefix, $baseDir, $plugin) {
    throw new Exception_NotSupported("Not supported with Composer class loader.");
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
    $this->finder->addClassMap(array($class => $file), $override);
  }
}
