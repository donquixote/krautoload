<?php

namespace Krautoload\Tests;

use Krautoload as k;

class PluggableClassLoaderTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var k\RegistrationHub
   */
  protected $hub;

  /**
   * @var k\ClassLoader_Pluggable_Interface
   */
  protected $loader;

  public function setUp() {
    $this->loader = new k\ClassLoader_Pluggable();
    $this->hub = new k\RegistrationHub($this->loader);
  }

  public function testLoadClass() {
    $this->hub->addClassMap(array(
      'ClassMap\Foo\Bar' => $this->getFixturesSubdir('src-classmap') . '/classmap-foo-bar.php',
      'ClassMap\Foo\Baz' => $this->getFixturesSubdir('src-classmap') . '/classmap-foo-baz.php',
    ));
    $this->hub->addNamespacePSR0('Namespaced2', $this->getFixturesSubdir('src-psr0'));
    $this->hub->addPrefixPEAR('Pearlike2', $this->getFixturesSubdir('src-psr0'));
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $this->getFixturesSubdir('src-psrx'));
    $this->assertLoadClass('ClassMap\Foo\Bar');
    $this->assertLoadClass('ClassMap\Foo\Baz');
    $this->assertLoadClass('Namespaced2\Foo');
    $this->assertLoadClass('Pearlike2_Foo');
    $this->assertLoadClass('MyVendor\MyPackage\Foo\Bar');
  }

  public function testExotic() {
    $plugin = new k\PrefixPathPlugin_Exotic_CamelSwap();
    $this->hub->addPrefixPlugin('CamelSwap', $this->getFixturesSubdir('camel-swap'), $plugin);
    // The class is in tests/fixtures/camel-swap/controller/page/help.php.
    $this->assertLoadClass('CamelSwap_HelpPageController');
  }

  public function testUnderscoreSafe() {
    // Using the paranoid plugin.
    $this->hub->addNamespacePSR0('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0'));
    $this->assertLoadClass('Namespace_With_Underscore\Sub_Namespace\Foo_Bar');
    // This would break in other class loaders.
    $this->assertNotLoadClass('Namespace_With_Underscore\Sub_Namespace\Foo\Bar');
  }

  public function testUnderscoreUnsafe() {
    // Using the non-paranoid plugin.
    $plugin = new k\NamespacePathPlugin_ShallowPSR0_NoConflict();
    $this->hub->addNamespacePlugin('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0/Namespace_With_Underscore'), $plugin);
    $this->assertLoadClass('Namespace_With_Underscore\Sub_Namespace\Foo_BarUnsafe');
    try {
      $this->assertNotLoadClass('Namespace_With_Underscore\Sub_Namespace\Foo\BarUnsafe');
    }
    catch (\Exception $e) {
      $this->assertEquals($e->getMessage(), 'Cannot redefine class.');
      return;
    }
    $this->fail('The NoConflict loader plugin is expected to break with duplicate class definition.');
  }

  public function testUseIncludePath() {

    // Register a plugin that can handle include path.
    $plugin = new k\PrefixPathPlugin_ShallowPEAR_UseIncludePath();
    $this->hub->addPrefixPlugin('', '', $plugin);

    $this->assertNotLoadClass('Foo', "Class 'Foo' still undefined after ->loadClass() without include path.");

    // Remember original include path.
    $includePath = get_include_path();
    set_include_path($this->getFixturesSubdir('includepath') . PATH_SEPARATOR . $includePath);

    $this->loader->loadClass('Foo');
    $this->assertClassDefined('Foo', "Class 'Foo' successfully loaded after ->loadClass() with include path.");

    // Revert include path to its original value.
    set_include_path($includePath);
  }

  /**
   * @dataProvider getLoadClassFromFallbackTests
   */
  public function testLoadClassFromFallback($class, $from = '') {
    $this->hub->addNamespacePSR0('Namespaced2', $this->getFixturesSubdir('src-psr0'));
    $this->hub->addPrefixPEAR('Pearlike2', $this->getFixturesSubdir('src-psr0'));
    // addPrefixPSR0 applies to namespaced and non-namespaced classes.
    $this->hub->addPrefixPSR0('', $this->getFixturesSubdir('fallback'));

    $this->assertClassUndefined($class);
    $this->loader->loadClass($class);
    $this->assertClassDefined($class, "Class '$class' successfully loaded$from.");
  }

  public function getLoadClassFromFallbackTests() {
    return array(
      array('Namespaced2\\Baz'),
      array('Pearlike2_Baz'),
      array('Namespaced2\\FooBar', ' from fallback dir'),
      array('Pearlike2_FooBar',    ' from fallback dir'),
    );
  }

  /**
   * @dataProvider getLoadClassNamespaceCollisionTests
   */
  public function testLoadClassNamespaceCollision($namespaces, $class, $message) {

    $this->hub->addPrefixesPSR0($namespaces);

    $this->assertClassUndefined($class);
    $this->loader->loadClass($class);
    $this->assertClassDefined($class, $message);
  }

  public function getLoadClassNamespaceCollisionTests() {
    return array(
      array(
        array(
          'NamespaceCollision\\C' => $this->getFixturesSubdir('alpha'),
          'NamespaceCollision\\C\\B' => $this->getFixturesSubdir('beta'),
        ),
        'NamespaceCollision\C\Foo',
        '->loadClass() loads NamespaceCollision\C\Foo from alpha.',
      ),
      array(
        array(
          'NamespaceCollision\\C\\B' => $this->getFixturesSubdir('beta'),
          'NamespaceCollision\\C' => $this->getFixturesSubdir('alpha'),
        ),
        'NamespaceCollision\C\Bar',
        '->loadClass() loads NamespaceCollision\C\Bar from alpha.',
      ),
      array(
        array(
          'NamespaceCollision\\C' => $this->getFixturesSubdir('alpha'),
          'NamespaceCollision\\C\\B' => $this->getFixturesSubdir('beta'),
        ),
        'NamespaceCollision\C\B\Foo',
        '->loadClass() loads NamespaceCollision\C\B\Foo from beta.',
      ),
      array(
        array(
          'NamespaceCollision\\C\\B' => $this->getFixturesSubdir('beta'),
          'NamespaceCollision\\C' => $this->getFixturesSubdir('alpha'),
        ),
        'NamespaceCollision\C\B\Bar',
        '->loadClass() loads NamespaceCollision\C\B\Bar from beta.',
      ),
      array(
        array(
          'PrefixCollision_C_' => $this->getFixturesSubdir('alpha'),
          'PrefixCollision_C_B_' => $this->getFixturesSubdir('beta'),
        ),
        'PrefixCollision_C_Foo',
        '->loadClass() loads PrefixCollision_C_Foo from alpha.',
      ),
      array(
        array(
          'PrefixCollision_C_B_' => $this->getFixturesSubdir('beta'),
          'PrefixCollision_C_' => $this->getFixturesSubdir('alpha'),
        ),
        'PrefixCollision_C_Bar',
        '->loadClass() loads PrefixCollision_C_Bar from alpha.',
      ),
      array(
        array(
          'PrefixCollision_C_' => $this->getFixturesSubdir('alpha'),
          'PrefixCollision_C_B_' => $this->getFixturesSubdir('beta'),
        ),
        'PrefixCollision_C_B_Foo',
        '->loadClass() loads PrefixCollision_C_B_Foo from beta.',
      ),
      array(
        array(
          'PrefixCollision_C_B_' => $this->getFixturesSubdir('beta'),
          'PrefixCollision_C_' => $this->getFixturesSubdir('alpha'),
        ),
        'PrefixCollision_C_B_Bar',
        '->loadClass() loads PrefixCollision_C_B_Bar from beta.',
      ),
    );
  }

  protected function assertLoadClass($class, $message = NULL) {
    $this->assertClassUndefined($class);
    $this->loader->loadClass($class);
    $this->assertClassDefined($class, $message);
  }

  protected function assertNotLoadClass($class, $message = NULL) {
    if (!isset($message)) {
      $message = "Class '$class' is still undefined after ->loadClass().";
    }
    $this->assertClassUndefined($class);
    $this->loader->loadClass($class);
    $this->assertClassUndefined($class, $message);
  }

  protected function assertClassUndefined($class, $message = NULL) {
    if (!isset($message)) {
      $message = "Class '$class' is not defined before ->loadClass().";
    }
    $this->assertFalse(k\Util::classIsDefined($class), $message);
  }

  protected function assertClassDefined($class, $message = NULL) {
    if (!isset($message)) {
      $message = "Class '$class' was successfully loaded with ->loadClass().";
    }
    $this->assertTrue(k\Util::classIsDefined($class), $message);
  }

  protected function getFixturesSubdir($suffix) {
    return __DIR__ . '/../fixtures/' . $suffix;
  }
}
