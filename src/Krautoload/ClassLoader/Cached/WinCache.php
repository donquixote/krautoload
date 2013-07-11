<?php

namespace Krautoload;

class ClassLoader_Cached_WinCache extends ClassLoader_Cached_AbstractPrefixBased {

  /**
   * @throws \RuntimeException
   */
  protected function checkRequirements() {
    if (!extension_loaded('wincache')) {
      throw new \RuntimeException('Unable to use WinCache class loader, as WinCache is not enabled.');
    }
  }

  /**
   * @inheritdoc
   */
  public function loadClass($class) {

    // @todo: What is stored in WinCache, if the class does not exist? False or NULL?
    if (FALSE === $file = wincache_ucache_get($this->prefix . $class)) {
      // Resolve cache miss.
      wincache_ucache_set($this->prefix . $class, $file = $this->decorated->loadClassGetFile($class), 0);
    }
    else {
      require $file;
    }
  }
}
