<?php

namespace Krautoload;

interface FinderPlugin_Interface {

  /**
   * @param InjectedAPI $api
   * @param string $prefix
   * @param string $dir
   * @param string $suffix
   *
   * @return boolean
   *   TRUE, if the class was found.
   */
  function pluginFindFile($api, $prefix, $dir, $suffix);
}
