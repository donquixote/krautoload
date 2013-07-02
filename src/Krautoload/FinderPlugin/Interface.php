<?php

namespace Krautoload;

interface FinderPlugin_Interface {

  /**
   * @param ClassFinderAPI_Interface $api
   * @param string $prefix
   * @param string $dir
   * @param string $suffix
   *
   * @return boolean
   *   TRUE, if the $api did return TRUE for one candidate file.
   */
  function pluginFindFile($api, $prefix, $dir, $suffix);

  /**
   * Shortcut to directly load the class, with no $api in the way.
   *
   * @param string $class
   * @param string $prefix
   * @param string $dir
   * @param string $suffix
   *
   * @return boolean
   *   TRUE, if the class was loaded.
   */
  function pluginLoadClass($class, $prefix, $dir, $suffix);
}
