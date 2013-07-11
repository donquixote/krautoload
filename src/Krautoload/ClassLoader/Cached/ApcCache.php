<?php

namespace Krautoload;

class ClassLoader_Cached_ApcCache extends ClassLoader_Cached_AbstractPrefixBased {

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
