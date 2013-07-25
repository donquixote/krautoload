<?php

namespace Krautoload;


abstract class ClassLoader_Composer_Abstract extends ClassLoader_AbstractClassMap implements ClassLoader_Composer_Interface {

  protected $useIncludePath = false;

  /**
   * @inheritdoc
   */
  public function getClassMap() {
    return $this->classMap;
  }

  /**
   * @inheritdoc
   */
  public function addMultiple($prefixes, $prepend = FALSE) {
    foreach ($prefixes as $prefix => $paths) {
      $this->add($prefix, $paths, $prepend);
    }
  }

  /**
   * @inheritdoc
   */
  public function setUseIncludePath($useIncludePath) {
    $this->useIncludePath = $useIncludePath;
  }

  /**
   * @inheritdoc
   */
  public function getUseIncludePath() {
    return $this->useIncludePath;
  }

  /**
   * @inheritdoc
   */
  public function loadClass($class) {
    if ($file = $this->findFile($class)) {
      include $file;
      return true;
    }
  }

}