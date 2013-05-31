<?php

namespace Krautoload;

class FinderPlugin_ShallowPEAR implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }
}
