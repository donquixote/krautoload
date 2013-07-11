<?php

namespace Krautoload;

/**
 * WinCacheClassLoader implements a wrapping autoloader cached in WinCache.
 *
 * It expects an object implementing a findFile method to find the file. This
 * allow using it as a wrapper around the other loaders of the component (the
 * ClassLoader and the UniversalClassLoader for instance) but also around any
 * other autoloader following this convention (the Composer one for instance)
 *
 *     $loader = new ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     $cachedLoader = new WinCacheClassLoader('my_prefix', $loader);
 *
 *     // activate the cached autoloader
 *     $cachedLoader->register();
 *
 *     // eventually deactivate the non-cached loader if it was registered previously
 *     // to be sure to use the cached one.
 *     $loader->unregister();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kris Wallsmith <kris@symfony.com>
 * @author Artem Ryzhkov <artem@smart-core.org>
 */
class ClassLoader_Cached_WinCache extends ClassLoader_Cached_Abstract {

  private $prefix;

  /**
   * Constructor.
   *
   * @param string $prefix      The WinCache namespace prefix to use.
   * @param ClassLoader_Interface  $decorated   A class loader object that implements the findFile() method.
   *
   * @throws \RuntimeException
   */
  public function __construct($prefix, ClassLoader_Interface $decorated) {

    if (!extension_loaded('wincache')) {
      throw new \RuntimeException('Unable to use WinCacheClassLoader as WinCache is not enabled.');
    }

    $this->prefix = $prefix;
    parent::__construct($decorated);
  }

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
