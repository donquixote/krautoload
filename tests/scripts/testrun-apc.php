<?php
/**
 * @file
 *
 * File to test the bootstrap.
 */

require_once 'src/Krautoload.php';
$krautoload = Krautoload::start(array(
  'cache' => 'ApcCache',
  'cache_prefix' => $prefix = 'krautoload-test-07a9812c3469d38u567c64650',
));

$krautoload->addClassMap(array(
  'ClassMap\Foo\Bar' => 'tests/fixtures/src-classmap/classmap-foo-bar.php',
));
$krautoload->addNamespacePSR0('Namespaced2', 'tests/fixtures/src-psr0');
$krautoload->addPrefixPEAR('Pearlike2', 'tests/fixtures/src-psr0');
$krautoload->addNamespacePSRX('MyVendor\MyPackage', 'tests/fixtures/src-psrx');

new ClassMap\Foo\Bar;
new Namespaced2\Foo;
new Pearlike2_Foo;
new MyVendor\MyPackage\Foo\Bar;

if (class_exists('ClassMap\Foo\Baz')) {
  print "Class 'ClassMap\Foo\Bar' exists, although it should not.\n";
}

// Manually add the class to the APC cache.
apc_store($prefix . 'ClassMap\Foo\Baz', 'tests/fixtures/src-classmap/classmap-foo-baz.php');

new ClassMap\Foo\Baz;

echo 'SUCCESS';