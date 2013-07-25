<?php

namespace Krautoload;


interface ClassLoader_Composer_Interface extends ClassLoader_Interface {

  public function getClassMap();

  /**
   * @param array $classMap Class to filename map
   */
  public function addClassMap(array $classMap);

  /**
   * Registers a set of classes, merging with any others previously set.
   *
   * @param string       $prefix  The classes prefix
   * @param array|string $paths   The location(s) of the classes
   * @param bool         $prepend Prepend the location(s)
   */
  public function add($prefix, $paths, $prepend = false);

  public function addMultiple($prefixes, $prepend = false);

  /**
   * Turns on searching the include path for class files.
   *
   * @param bool $useIncludePath
   */
  public function setUseIncludePath($useIncludePath);

  /**
   * Can be used to check if the autoloader uses the include path to check
   * for classes.
   *
   * @return bool
   */
  public function getUseIncludePath();

}