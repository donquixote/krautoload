<?php

namespace Krautoload;

class ClassLoader_NoCache extends ClassLoader_Abstract {

  protected $finder;

  /**
   * @param ClassFinder_Interface $finder
   *   The object that does the actual class finding.
   */
  function __construct($finder) {
    $this->finder = $finder;
  }

  /**
   * Callback for class loading. This will include ("require") the file found.
   *
   * @param string $class
   *   The class to load.
   */
  function loadClass($class) {
    $api = new InjectedAPI_ClassFinder_LoadClass($class);
    // $api has a ->suggestFile($file) method, which returns TRUE if the
    // suggested file exists.
    // The $finder->findFile() method is supposed to suggest a number of files
    // to the $api, until one is successful, and then return TRUE. Or return
    // FALSE, if nothing was found.
    if ($this->finder->apiFindFile($api, $class)) {
      return TRUE;
    }
    return FALSE;
  }
}
