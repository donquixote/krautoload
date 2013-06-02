<?php

namespace Krautoload;

/**
 * This plugin is aimed at PSR-0 implementors which either
 * - know exactly what they are doing, and use file_exists() responsibly, OR
 * - don't care about a theoretical risk, and are happy about every millisecond
 *   they can get.
 *
 * Interestingly, most existing PSR-0 loaders take exactly this risk,
 * without even telling you.
 *
 * The plugin assumes a shallow PSR-0 mapping, where
 * 1) Whenever a class is requested (*), and this class is within the registered
 *    namespace, and the file the class is mapped to does exist, then this file
 *    must contain the requested class.
 *    As a consequence: Whenever two or more classes are requested (*) during a
 *    request, and those classes are within the registered namespace, and those
 *    classes map to the same *.php file within the registered directory, and
 *    this PHP file does exist, then this file defines ALL classes that are
 *    being requested this way.
 * 2) Each of the *.php files within the registered directory defines only
 *    the class(es) we expect, as in point (1), and none other.
 *
 * E.g. if the registered namespace is "MyVendor\MyPackage", and the following
 * classes are being requested,
 * - "MyVendor\MyPackage\Foo\Bar\Baz", and
 * - "MyVendor\MyPackage\Foo\Bar_Baz",
 * and the following file does exist within the registered directory:
 * - "../Foo/Bar/Baz.php,
 * then this file must define both of the requested classes.
 *
 * The above condition is quite theoretical, attempting to not be more
 * restrictive than necessary.
 *
 * (*) A class can be "requested" via
 *    - class_exists(*, TRUE)
 *    - Instantiation with "new .."
 *    - Static method calls, or static class attributes.
 *    - "extends" statement in another class definitions.
 *    - ReflectionClass.
 *
 * The plugin can thus
 * - use include instead of include_once.
 *   Even though the file could (theoretically) define more than one class, the
 *   class loader will only be fired once, because after that all those classes
 *   are already defined.
 * - skip the class_exists() check.
 */
class FinderPlugin_ShallowPSR0_NoConflict implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    // We need to replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($suffix, DIRECTORY_SEPARATOR)) {
      $suffix = substr($suffix, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($suffix, $pos));
    }
    else {
      $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    }
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }
}
