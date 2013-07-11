<?php

namespace Krautoload;

abstract class ClassLoader_Cached_AbstractPrefixBased extends ClassLoader_Cached_Abstract {

  protected $prefix;

  /**
   *
   * @param ClassLoader_Interface $decorated
   *   Another ClassFinder to delegate to, if the class is not in the cache.
   * @param string $prefix
   *   A prefix for the storage key in APC, XCache, etc.
   * @throws \RuntimeException
   */
  function __construct(ClassLoader_Interface $decorated, $prefix) {

    $this->checkRequirements();

    $this->prefix = $prefix;
    parent::__construct($decorated);
  }

  abstract protected function checkRequirements();

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
