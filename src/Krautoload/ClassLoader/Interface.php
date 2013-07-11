<?php

namespace Krautoload;

interface ClassLoader_Interface {

  /**
   * Registers this instance as an autoloader.
   *
   * @param boolean $prepend
   *   If TRUE, the loader will be prepended. Otherwise, it will be appended.
   */
  function register($prepend = FALSE);

  /**
   * Unregister this instance as an autoloader.
   */
  function unregister();

  /**
   * Callback for class loading. This will include ("require") the file found.
   *
   * @param string $class
   *   The class to load.
   */
  function loadClass($class);

  /**
   * For compatibility, it is possible to use the class loader as a finder.
   * This will only ever return the first file found, even if that is wrong.
   *
   * @param string $class
   *   The class to find.
   *
   * @return string
   *   File where the class is assumed to be.
   */
  function findFile($class);

  /**
   * Load a class, and return the file that was successful.
   *
   * @param string $class
   *   The class to load.
   *
   * @return string
   *   The file that defined the class.
   */
  function loadClassGetFile($class);

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param InjectedAPI_ClassFinder_Interface $api
   *   API object with a suggestFile() method.
   *   We are supposed to call $api->suggestFile($file) with all suggestions we
   *   can find, until it returns TRUE. Once suggestFile() returns TRUE, we stop
   *   and return TRUE as well. The $file will be in the $api object, so we
   *   don't need to return it.
   * @param string $class
   *   The name of the class, with all namespaces prepended.
   *   E.g. Some\Namespace\Some\Class
   *
   * @return TRUE|NULL
   *   TRUE, if we found the file for the class.
   *   That is, if the $api->suggestFile($file) method returned TRUE one time.
   *   NULL, if we have no more suggestions.
   */
  public function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class);
}
