<?php

namespace Krautoload;

class ClassLoader_Cached_ApcCache extends ClassLoader_Cached_Abstract {

  protected $prefix;

  /**
   *
   * @param ClassFinder_Interface $finder
   *   Another ClassFinder to delegate to, if the class is not in the cache.
   * @param string $prefix
   *   A prefix for the storage key in APC.
   * @throws \RuntimeException
   */
  function __construct(ClassFinder_Interface $finder, $prefix) {
    if (!extension_loaded('apc') || !function_exists('apc_store')) {
      throw new \RuntimeException('Unable to use Krautoload\ClassLoader_ApcCache, because APC is not enabled.');
    }
    $this->prefix = $prefix;
    parent::__construct($finder);
  }

  /**
   * Set the APC prefix after a flush cache.
   *
   * @param string $prefix
   *   A prefix for the storage key in APC.
   */
  function setApcPrefix($prefix) {
    $this->prefix = $prefix;
  }

  /**
   * @inheritdoc
   */
  function loadClass($class) {

    // @todo: What is stored in APC cache, if the class does not exist? False or NULL?
    if (
      (FALSE === $file = apc_fetch($this->prefix . $class)) ||
      (!empty($file) && !is_file($file))
    ) {
      // Resolve cache miss.
      apc_store($this->prefix . $class, $file = $this->finder->loadClassGetFile($class));
    }
    else {
      require $file;
    }
  }
}
