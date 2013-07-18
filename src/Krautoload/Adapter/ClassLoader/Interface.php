<?php

namespace Krautoload;

interface Adapter_ClassLoader_Interface {

  /**
   * @return ClassLoader_Pluggable_Interface
   */
  function getFinder();

  /**
   * @param callback $callback
   *   Registration callback, which takes as an argument the registration adapter.
   */
  function krautoloadCallback($callback);

  /**
   * @param string $file
   *   Path to a PHP file that, on inclusion, returns a registration callback.
   */
  function krautoloadFile($file);

  /**
   * @param string $dir
   *   Vendor directory of a project using composer.
   *   This allows to use Krautoload for composer-based PHP projects.
   */
  function composerVendorDir($dir);

  /**
   * Registers Composer-style PSR-0 prefixes.
   * These prefixes can apply to both namespaced and non-namespaced classes.
   *
   * @param array $prefixes
   *   Prefixes to add
   */
  function addPrefixesPSR0(array $prefixes);

  /**
   * Registers a Composer-style PSR-0 prefix.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $rootDirs
   *   The location(s) of the classes
   */
  function addPrefixPSR0($prefix, $rootDirs);

  /**
   * Registers Composer-style PSR-0 prefixes.
   * These prefixes can apply to both namespaced and non-namespaced classes.
   * Alias for addPrefixesPSR0(), to be more consistent with other loaders.
   *
   * @param array $prefixes
   *   Prefixes to add
   */
  function addPrefixes(array $prefixes);

  /**
   * Registers a Composer-style PSR-0 prefix.
   * This prefix can apply to both namespaced and non-namespaced classes.
   * Alias for addPrefixPSR0(), to be more consistent with other loaders.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $rootDirs
   *   The location(s) of the classes
   */
  function addPrefix($prefix, $rootDirs);

  /**
   * Adds PSR-0 namespaces.
   * This will only apply to namespaced classes, unless one of the namespaces
   * is the root namespace.
   *
   * @param array $namespaces
   */
  function addNamespacesPSR0(array $namespaces);

  /**
   * Adds a PSR-0 namespace,
   * This will only apply to namespaced classes, unless one of the namespaces
   * is the root namespace.
   *
   * @param string $namespace
   *   Namespace without leading or trailing separators.
   * @param array|string $rootDirs
   *   PSR-0 base directories associated with this namespace.
   */
  function addNamespacePSR0($namespace, $rootDirs);

  /**
   * Adds "Shallow PSR-0" namespaces.
   * This is a variation of PSR-0 without a more shallow directory structure.
   * This only exists "because we can", and because it is used in the
   * implementation of regular PSR-0.
   *
   * @param array $namespaces
   */
  function addNamespacesShallowPSR0(array $namespaces);

  /**
   * Adds a "Shallow PSR-0" namespace.
   * This is a variation of PSR-0 without a more shallow directory structure.
   * This only exists "because we can", and because it is used in the
   * implementation of regular PSR-0.
   *
   * @param string $namespace
   * @param array|string $baseDirs
   */
  function addNamespaceShallowPSR0($namespace, $baseDirs);

  /**
   * Adds PEAR prefixes.
   * This will only apply to non-namespaced classes.
   *
   * @param array $prefixes
   * @param bool $preventCollision
   *   If TRUE, then we will have to check class_exists() / interface_exists() /
   *   trait_exists() after inclusing the file.
   *   Set to TRUE if used as part of a PSR-0 mapping.
   */
  function addPrefixesPEAR(array $prefixes, $preventCollision = FALSE);

  /**
   * Adds a PEAR prefix.
   * This will only apply to non-namespaced classes.
   *
   * @param string $prefix
   *   The prefix, without trailing underscore.
   * @param array|string $rootDirs
   *   The PEAR directories associated with this prefix.
   * @param bool $preventCollision
   *   If TRUE, then we will have to check class_exists() / interface_exists() /
   *   trait_exists() after inclusing the file.
   *   Set to TRUE if used as part of a PSR-0 mapping.
   */
  function addPrefixPEAR($prefix, $rootDirs, $preventCollision = FALSE);

  /**
   * Adds a "Shallow PEAR" prefix.
   * This is a non-standard variation of PEAR with shallow directory structures.
   * This only exists "because we can", and because it is used in the
   * implementation of regular PEAR.
   *
   * @param array $prefixes
   * @param bool $preventCollision
   *   If TRUE, then we will have to check class_exists() / interface_exists() /
   *   trait_exists() after inclusing the file.
   *   Set to TRUE if used as part of a PSR-0 mapping.
   */
  function addPrefixesShallowPEAR(array $prefixes, $preventCollision = FALSE);

  /**
   * Adds a "Shallow PEAR" prefix.
   * This is a non-standard variation of PEAR with shallow directory structures.
   * This only exists "because we can", and because it is used in the
   * implementation of regular PEAR.
   *
   * @param string $prefix
   *   The prefix, without trailing underscore.
   * @param array|string $baseDirs
   *   The "Shallow PEAR" directories associated with this prefix.
   * @param bool $preventCollision
   *   If TRUE, then we will have to check class_exists() / interface_exists() /
   *   trait_exists() after inclusing the file.
   *   Set to TRUE if used as part of a PSR-0 mapping.
   */
  function addPrefixShallowPEAR($prefix, $baseDirs, $preventCollision = FALSE);

  /**
   * Adds PSR-X namespaces.
   * This will only apply to namespaced classes, unless one of the namespaces
   * is the root namespace.
   *
   * @param array $namespaces
   */
  function addNamespacesPSRX(array $namespaces);

  /**
   * Adds a PSR-X namespace,
   * This will only apply to namespaced classes, unless one of the namespaces
   * is the root namespace.
   *
   * @param string $namespace
   *   Namespace without leading or trailing separators.
   * @param array|string $baseDirs
   *   PSR-X base directories associated with this namespace.
   */
  function addNamespacePSRX($namespace, $baseDirs);

  /**
   * Adds a namespace plugin.
   *
   * @param string $namespace
   * @param string $baseDir
   * @param NamespacePathPlugin_Interface $plugin
   */
  function addNamespacePlugin($namespace, $baseDir, $plugin);

  /**
   * Adds a prefix plugin.
   *
   * @param string $prefix
   * @param string $baseDir
   * @param PrefixPathPlugin_Interface $plugin
   */
  function addPrefixPlugin($prefix, $baseDir, $plugin);

  /**
   * @param array $classMap
   *   An array where the keys are classes, and the values are filenames.
   * @param bool $override
   *   If TRUE, classes in the new class map will override old values.
   */
  function addClassMap(array $classMap, $override = FALSE);

  /**
   * @param string $class
   *   The class.
   * @param string $file
   *   The file where this class is expected to be found.
   * @param bool $override
   *   If TRUE, the $file argument will override existing values.
   */
  function addClassFile($class, $file, $override = TRUE);
}
