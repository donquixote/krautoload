<?php

namespace Krautoload;

/**
 * This plugin assumes a shallow PSR-0 mapping, where
 * 1) Whenever a class is requested (*), then this class is NOT within a
 *    sub-namespace of the registered namespace.
 * 2) Each of the *.php files within the registered directory defines exactly
 *    the class we expect, as in point (1), and none other.
 *
 * E.g. if the registered namespace is "MyVendor\MyPackage", then
 * - the following is allowed: "MyVendor\MyPackage\Foo_Bar_Baz", but
 * - the following is not allowed: "MyVendor\MyPackage\Foo\Bar\Baz", or
 *   "MyVendor\MyPackage\Foo\Bar_Baz".
 *
 * (*) A class can be "requested" via
 *    - class_exists(*, TRUE)
 *    - Instantiation with "new .."
 *    - Static method calls, or static class attributes.
 *    - "extends" statement in another class definitions.
 *    - ReflectionClass.
 */
class FinderPlugin_ShallowPSR0_AllUnderscore implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    // We need to replace all underscores in the suffix part.
    $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }
}
