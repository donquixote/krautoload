<?php

namespace Krautoload;

/**
 * This plugin assumes a shallow PSR-0 mapping, where
 * 1) Any class defined in a file under the registered directory contains NO
 *    underscore after the last namespace separator.
 * 2) Any *.php file under the requested directory contains NO underscore in the
 *    filename.
 *
 * E.g. if the registered namespace is "MyVendor\MyPackage", then
 * - the following is allowed: "MyVendor\MyPackage\Foo\Bar\Baz", but
 * - the following is NOT allowed: "MyVendor\MyPackage\Foo_Bar_Baz", or
 *   "MyVendor\MyPackage\Foo\Bar_Baz".
 *
 * (*) A class can be "requested" via
 *    - class_exists(*, TRUE)
 *    - Instantiation with "new .."
 *    - Static method calls, or static class attributes.
 *    - "extends" statement in another class definitions.
 *    - ReflectionClass.
 *
 * The plugin can thus use the same implementation as PSR-X:
 * It does not need to replace any underscores, it can use include instead of
 * include_once, and it does not need any class_exists() check.
 */
class NamespacePathPlugin_ShallowPSR0_NoUnderscore extends NamespacePathPlugin_PSRX {}
