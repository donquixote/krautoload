<?php

namespace Krautoload;

class ClassLoader_Cached_XCache extends ClassLoader_Cached_AbstractPrefixBased {

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
      xcache_set($this->prefix . $class, $this->finder->loadClassGetFile($class));
    }
  }
}
