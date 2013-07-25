<?php

namespace Krautoload\Tests;

use Krautoload as k;

/**
 * @runTestsInSeparateProcesses
 */
class PluggableClassLoaderTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider getLoaders
   */
  public function testLoadClass(k\Adapter_ClassLoader_Interface $adapter) {
    $adapter->addClassMap(array(
      'ClassMap\Foo\Bar' => $this->getFixturesSubdir('src-classmap') . '/classmap-foo-bar.php',
      'ClassMap\Foo\Baz' => $this->getFixturesSubdir('src-classmap') . '/classmap-foo-baz.php',
    ));
    $adapter->addNamespacePSR0('Namespaced2', $this->getFixturesSubdir('src-psr0'));
    $adapter->addPrefixPEAR('Pearlike2', $this->getFixturesSubdir('src-psr0'));
    if ($adapter->supportsPSRX) {
      $adapter->addNamespacePSRX('MyVendor\MyPackage', $this->getFixturesSubdir('src-psrx'));
    }
    $this->assertLoadClass($adapter, 'ClassMap\Foo\Bar');
    $this->assertLoadClass($adapter, 'ClassMap\Foo\Baz');
    $this->assertLoadClass($adapter, 'Namespaced2\Foo');
    $this->assertLoadClass($adapter, 'Pearlike2_Foo');
    if ($adapter->supportsPSRX) {
      $this->assertLoadClass($adapter, 'MyVendor\MyPackage\Foo\Bar');
    }
  }

  /**
   * @dataProvider getLoaders
   */
  public function testExotic(k\Adapter_ClassLoader_Interface $adapter) {
    if (!$adapter->supportsPlugins) {
      $this->assertTrue(TRUE);
      return;
    }
    $plugin = new k\PrefixPathPlugin_Exotic_CamelSwap();
    $adapter->addPrefixPlugin('CamelSwap', $this->getFixturesSubdir('camel-swap'), $plugin);
    // The class is in tests/fixtures/camel-swap/controller/page/help.php.
    $this->assertLoadClass($adapter, 'CamelSwap_HelpPageController');
  }

  /**
   * @dataProvider getLoaders
   */
  public function testUnderscoreSafe(k\Adapter_ClassLoader_Interface $adapter) {
    if (!$adapter->isSafePSR0) {
      $this->assertTrue(TRUE);
      return;
    }
    // Using the paranoid plugin.
    $adapter->addNamespacePSR0('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0'));
    $this->assertLoadClass($adapter, 'Namespace_With_Underscore\Sub_Namespace\Foo_Bar');
    // This would break in other class loaders.
    $this->assertNotLoadClass($adapter, 'Namespace_With_Underscore\Sub_Namespace\Foo\Bar');
  }

  /**
   * @dataProvider getLoaders
   */
  public function testUnderscoreUnsafe(k\Adapter_ClassLoader_Interface $adapter) {
    if (!$adapter->supportsPlugins) {
      $this->assertTrue(TRUE);
      return;
    }
    // Using the non-paranoid plugin.
    $plugin = new k\NamespacePathPlugin_ShallowPSR0_NoConflict();
    $adapter->addNamespacePlugin('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0/Namespace_With_Underscore'), $plugin);
    $this->assertLoadClass($adapter, 'Namespace_With_Underscore\Sub_Namespace\Foo_BarUnsafe');
    try {
      $this->assertNotLoadClass($adapter, 'Namespace_With_Underscore\Sub_Namespace\Foo\BarUnsafe');
    }
    catch (\Exception $e) {
      $this->assertEquals($e->getMessage(), 'Cannot redefine class.');
      return;
    }
    $this->fail('The NoConflict loader plugin is expected to break with duplicate class definition.');
  }

  /**
   * @dataProvider getLoaders
   */
  public function testUseIncludePath(k\Adapter_ClassLoader_Interface $adapter) {
    if (!$adapter->supportsPlugins) {
      $this->assertTrue(TRUE);
      return;
    }

    // Register a plugin that can handle include path.
    $plugin = new k\PrefixPathPlugin_ShallowPEAR_UseIncludePath();
    $adapter->addPrefixPlugin('', '', $plugin);

    $this->assertNotLoadClass($adapter, 'Foo', "Class 'Foo' still undefined after ->loadClass() without include path.");

    // Remember original include path.
    $includePath = get_include_path();
    set_include_path($this->getFixturesSubdir('includepath') . PATH_SEPARATOR . $includePath);

    $adapter->getFinder()->loadClass('Foo');
    $this->assertClassDefined('Foo', "Class 'Foo' successfully loaded after ->loadClass() with include path.");

    // Revert include path to its original value.
    set_include_path($includePath);
  }

  /**
   * @dataProvider getLoadClassFromFallbackTests
   */
  public function testLoadClassFromFallback(k\Adapter_ClassLoader_Interface $adapter, $class, $from = '') {
    $adapter->addNamespacePSR0('Namespaced2', $this->getFixturesSubdir('src-psr0'));
    $adapter->addPrefixPEAR('Pearlike2', $this->getFixturesSubdir('src-psr0'));
    // addPrefixPSR0 applies to namespaced and non-namespaced classes.
    $adapter->addPrefixPSR0('', $this->getFixturesSubdir('fallback'));

    $this->assertClassUndefined($class);
    $adapter->getFinder()->loadClass($class);
    $this->assertClassDefined($class, "Class '$class' successfully loaded$from.");
  }

  public function getLoadClassFromFallbackTests() {
    return $this->prepareArgs(array(
      array('Namespaced2\\Baz'),
      array('Pearlike2_Baz'),
      array('Namespaced2\\FooBar', ' from fallback dir'),
      array('Pearlike2_FooBar',    ' from fallback dir'),
    ));
  }

  /**
   * @dataProvider getLoadClassNamespaceCollisionTests
   */
  public function testLoadClassNamespaceCollision(k\Adapter_ClassLoader_Interface $adapter, $namespaces, $class, $message) {

    $adapter->addPrefixesPSR0($namespaces);

    $this->assertClassUndefined($class);
    $adapter->getFinder()->loadClass($class);
    $this->assertClassDefined($class, $message);
  }

  public function getLoadClassNamespaceCollisionTests() {
    return $this->prepareArgs(array(
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
    ));
  }

  protected function prepareArgs($args_all) {
    $result = array();
    foreach ($this->getLoaders() as $loader_args) {
      foreach ($args_all as $args) {
        $result[] = array_merge($loader_args, $args);
      }
    }
    return $result;
  }

  protected function assertLoadClass(k\Adapter_ClassLoader_Interface $adapter, $class, $message = NULL) {
    $this->assertClassUndefined($class);
    $adapter->getFinder()->loadClass($class);
    $this->assertClassDefined($class, $message);
  }

  protected function assertNotLoadClass(k\Adapter_ClassLoader_Interface $adapter, $class, $message = NULL) {
    if (!isset($message)) {
      $message = "Class '$class' is still undefined after ->loadClass().";
    }
    $this->assertClassUndefined($class);
    $adapter->getFinder()->loadClass($class);
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

  public function getLoaders() {
    $loaders = array();

    $adapter = k\Adapter_ClassLoader_Pluggable::start();
    $adapter->supportsPSRX = TRUE;
    $adapter->supportsPlugins = TRUE;
    $adapter->isSafePSR0 = TRUE;
    $loaders[] = array($adapter);

    $adapter = k\Adapter_ClassLoader_Composer::start();
    $adapter->supportsPSRX = FALSE;
    $adapter->supportsPlugins = FALSE;
    $adapter->isSafePSR0 = FALSE;
    $loaders[] = array($adapter);

    return $loaders;
  }
}
