<?php

namespace Krautoload;

class ClassLoader_Cached_XCache extends ClassLoader_Cached_AbstractPrefixBased {

  /**
   * @throws \RuntimeException
   */
  protected function checkRequirements() {
    if (!extension_loaded('Xcache')) {
      throw new \RuntimeException('Unable to use XCache class loader, as XCache is not enabled.');
    }
  }

  /**
   * @inheritdoc
   */
  public function loadClass($class) {

    // @todo: What is stored in XCache, if the class does not exist? False or NULL?
    if (xcache_isset($this->prefix . $class)) {
      if (FALSE !== $file = xcache_get($this->prefix . $class)) {
        require $file;
      }
    }
    else {
      xcache_set($this->prefix . $class, $this->decorated->loadClassGetFile($class));
    }
  }
}
