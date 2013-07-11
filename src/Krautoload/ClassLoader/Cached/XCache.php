<?php

namespace Krautoload;

/**
 * XcacheClassLoader implements a wrapping autoloader cached in Xcache for PHP 5.3.
 *
 * It expects an object implementing a findFile method to find the file. This
 * allows using it as a wrapper around the other loaders of the component (the
 * ClassLoader and the UniversalClassLoader for instance) but also around any
 * other autoloader following this convention (the Composer one for instance)
 *
 *     $loader = new ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     $cachedLoader = new XcacheClassLoader('my_prefix', $loader);
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
 * @author Kim Hemsø Rasmussen <kimhemsoe@gmail.com>
 *
 * @api
 */
class ClassLoader_Cached_XCache extends ClassLoader_Cached_Abstract {

  private $prefix;

  /**
   * Constructor.
   *
   * @param string $prefix
   *   A prefix to create a namespace in Xcache
   * @param ClassLoader_Interface $decorated
   * @throws \RuntimeException
   */
  public function __construct($prefix, ClassLoader_Interface $decorated) {

    if (!extension_loaded('Xcache')) {
      throw new \RuntimeException('Unable to use XcacheClassLoader as Xcache is not enabled.');
    }

    $this->prefix = $prefix;
    parent::__construct($decorated);
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
      xcache_set($this->prefix . $class, $this->finder->loadClassGetFile($class));
    }
  }
}
