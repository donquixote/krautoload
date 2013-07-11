<?php

namespace Krautoload;

abstract class ClassLoader_Cached_AbstractPrefixBased extends ClassLoader_Cached_Abstract {

  protected $prefix;

  /**
   *
   * @param ClassFinder_Interface $finder
   *   Another ClassFinder to delegate to, if the class is not in the cache.
   * @param string $prefix
   *   A prefix for the storage key in APC, XCache, etc.
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
   * Set the cache prefix after a flush cache.
   *
   * @param string $prefix
   *   A prefix for the storage key in APC.
   */
  function setCachePrefix($prefix) {
    $this->prefix = $prefix;
  }
}
