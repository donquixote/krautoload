<?php
/**
 * @file
 *
 * File to test the bootstrap.
 */

require_once 'src/Krautoload.php';
$krautoload = Krautoload::start();

$krautoload->addClassMap(array(
  'ClassMap\Foo\Bar' => 'tests/fixtures/src-classmap/classmap-foo-bar.php',
  'ClassMap\Foo\Baz' => 'tests/fixtures/src-classmap/classmap-foo-baz.php',
));
$krautoload->addNamespacePSR0('Namespaced2', 'tests/fixtures/src-psr0');
$krautoload->addPrefixPEAR('Pearlike2', 'tests/fixtures/src-psr0');
$krautoload->addNamespacePSRX('MyVendor\MyPackage', 'tests/fixtures/src-psrx');

new ClassMap\Foo\Bar;
new ClassMap\Foo\Baz;
new Namespaced2\Foo;
new Pearlike2_Foo;
new MyVendor\MyPackage\Foo\Bar;

echo 'SUCCESS';