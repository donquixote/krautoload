<?php

namespace Krautoload;

class ClassLoader_Cached_WinCache extends ClassLoader_Cached_AbstractPrefixBased {

  /**
   * @inheritdoc
   */
  public function loadClass($class) {

    // @todo: What is stored in WinCache, if the class does not exist? False or NULL?
    if (FALSE === $file = wincache_ucache_get($this->prefix . $class)) {
      // Resolve cache miss.
      wincache_ucache_set($this->prefix . $class, $file = $this->finder->loadClassGetFile($class), 0);
    }
    else {
      require $file;
    }
  }
}
