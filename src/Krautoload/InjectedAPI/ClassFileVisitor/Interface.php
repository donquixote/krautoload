<?php

namespace Krautoload;

interface InjectedAPI_ClassFileVisitor_Interface {

  /**
   * Set the namespace, before searching for class files in this namespace.
   *
   * @param string $namespace
   *   Namespace without preceding separator, but with trailing separator.
   *   E.g. 'MyVendor\\' or 'MyVendor\\MyPackage\\',
   *   or just '' for the root namespace.
   */
  function setNamespace($namespace);

  /**
   * Get the current namespace.
   *
   * @return string
   *   The namespace for the current discovery operation.
   *   E.g. 'MyVendor\\' or 'MyVendor\\MyPackage\\',
   *   or just '' for the root namespace.
   */
  function getNamespace();

  /**
   * Get the absolute class name,
   * by appending the relative class name to the current namespace.
   *
   * @param $relativeClassName
   *   Class name relative to the current namespace.
   * @return string
   *   The fully-qualified class name.
   */
  function getClassName($relativeClassName);

  /**
   * A file was discovered that is expected to define the class.
   *
   * @param string $file
   *   The file that was found and is expected to contain a class.
   * @param string $relativeClassName
   *   The class name relative to the namespace previously specified with
   *   ->setNamespace().
   *   E.g. 'Foo\\Bar', so that the fully-qualified class name would be
   *   'MyVendor\\MyPackage\\Foo\\Bar',
   */
  function fileWithClass($file, $relativeClassName);

  /**
   * A file was discovered that may define any of the given classes.
   *
   * @param string $file
   *   The file that was found and may contain any or none of the classes.
   * @param array $relativeClassNames
   *   Array of relative class names for classes that *could* be in this file.
   *   With PSR-0, these can be different variations of the underscore.
   *   The one with the least underscores will always be at index 0.
   *   Class names are relative to the namespace previously specified with
   *   ->setNamespace().
   */
  function fileWithClassCandidates($file, array $relativeClassNames);
}
