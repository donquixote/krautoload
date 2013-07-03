<?php

namespace Krautoload;

interface PrefixPathPlugin_Interface {

  /**
   * Find files that could define the class, and report each of the candidates
   * to the ClassFinderAPI object.
   * Stop the operation, as soon as the ClassFinderAPI object returns TRUE.
   *
   * This type of plugin only fires with classes that have NO namespaces.
   *
   * @param ClassFinderAPI_Interface $api
   *   An object that gets told about files that could define the class.
   *   $api->getClass() will return the FQCN of the class we are looking for.
   * @param string $baseDir
   *   Physical base path associated with the logical base path.
   * @param string $relativePath
   *   Second part of the logical path built from the FQCN.
   *
   * @return boolean
   *   TRUE, if the $api did return TRUE for one candidate file.
   */
  function pluginFindFile($api, $baseDir, $relativePath);

  /**
   * Shortcut to directly load the class, with no $api in the way.
   *
   * @param string $class
   *   The class that is to be loaded.
   * @param string $baseDir
   *   Physical base path associated with the logical base path.
   * @param string $relativePath
   *   Second part of the logical path built from the FQCN.
   *
   * @return boolean
   *   TRUE, if the class was loaded.
   */
  function pluginLoadClass($class, $baseDir, $relativePath);
}
