<?php

namespace Krautoload\Tests;

use Krautoload as k;

/**
 * @runTestsInSeparateProcesses
 */
class ClassDiscoveryTest extends \PHPUnit_Framework_TestCase {

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoveryPSR0(k\Adapter_NamespaceInspector_Interface $adapter) {

    // Register PSR-0 namespace.
    $dir = $this->getFixturesSubdir('src-psr0');
    $adapter->addNamespacePSR0('Namespace_With_Underscore', $dir);
    $adapter->addNamespacePSR0('Namespaced', $dir);
    $adapter->addNamespacePSR0('Namespaced2', $dir);

    // Build the mock $api object.
    // We can't use the mock stuff shipped with PHPUnit, because we need a specific order of calls.
    $api = new k\InjectedAPI_ClassFileVisitor_Mock();

    // Run the discovery.
    $searchable = $adapter->buildSearchableNamespaces(array(
      'Namespace_With_Underscore',
      'Namespaced',
      'Namespace_With_Underscore\Sub_Namespace',
    ));
    $searchable->apiVisitClassFiles($api, TRUE);

    // Verify the result.
    $called = $api->mockGetCalled();

    // The InjectedAPI object is being told about the to-be-inspected namespace.
    $this->assertEquals($called[0], array('setNamespace', array('Namespace_With_Underscore\\')));

    // The two class files for this namespace may be discovered in any order.
    $this->assertArraySlice($called, 1, 2, array(
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespace_With_Underscore/Sub_Namespace/Foo/BarUnsafe.php',
          array(
            // Class names are given relative to the inspected namespace.
            // There are three class names that could be defined in the file.
            // They are expected in this exact order.
            'Sub_Namespace\Foo\BarUnsafe',
            'Sub_Namespace\Foo_BarUnsafe',
            'Sub_Namespace_Foo_BarUnsafe',
          ),
        ),
      ),
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespace_With_Underscore/Sub_Namespace/Foo/Bar.php',
          array(
            'Sub_Namespace\Foo\Bar',
            'Sub_Namespace\Foo_Bar',
            'Sub_Namespace_Foo_Bar',
          ),
        ),
      ),
    ));

    $this->assertEquals($called[3], array('setNamespace', array('Namespaced\\')));

    // The three class files for this namespace may be discovered in any order.
    $this->assertArraySlice($called, 4, 3, array(
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespaced/Foo.php',
          array(
            'Foo',
          ),
        ),
      ),
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespaced/Bar.php',
          array(
            'Bar',
          ),
        ),
      ),
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespaced/Baz.php',
          array(
            'Baz',
          ),
        ),
      ),
    ));

    $this->assertEquals($called[7], array('setNamespace', array('Namespace_With_Underscore\\Sub_Namespace\\')));

    // The two class files for this namespace may be discovered in any order.
    $this->assertArraySlice($called, 8, 2, array(
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespace_With_Underscore/Sub_Namespace/Foo/BarUnsafe.php',
          array(
            'Foo\BarUnsafe',
            'Foo_BarUnsafe',
          ),
        ),
      ),
      array(
        'fileWithClassCandidates',
        array(
          $dir . '/Namespace_With_Underscore/Sub_Namespace/Foo/Bar.php',
          array(
            'Foo\Bar',
            'Foo_Bar',
          ),
        ),
      ),
    ));
  }

  public function testDiscoveryPSR0Backward(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

  }

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoveryPSRX(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

    if (!$supportsPSRX) {
      $this->assertTrue(TRUE);
      return;
    }

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $adapter->addNamespacePSRX('MyVendor\MyPackage', $dir);

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $adapter->buildSearchableNamespaces(array('MyVendor\MyPackage'))->apiVisitClassFiles($api, TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor\MyPackage\\')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $dir . '/Foo/Bar.php',
          'Foo\Bar',
        ),
      ),
    ));
  }

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoveryPSRXChild(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

    if (!$supportsPSRX) {
      $this->assertTrue(TRUE);
      return;
    }

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $adapter->addNamespacePSRX('MyVendor\MyPackage', $dir);

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $adapter->buildSearchableNamespaces(array('MyVendor\MyPackage\Foo'))->apiVisitClassFiles($api, TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor\MyPackage\Foo\\')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $dir . '/Foo/Bar.php',
          'Bar',
        ),
      ),
    ));
  }

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoveryPSRXParent(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

    if (!$supportsPSRX) {
      $this->assertTrue(TRUE);
      return;
    }

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $adapter->addNamespacePSRX('MyVendor\MyPackage', $dir);


    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $adapter->buildSearchableNamespaces(array('MyVendor'))->apiVisitClassFiles($api, TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor\\')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $dir . '/Foo/Bar.php',
          'MyPackage\Foo\Bar',
        ),
      ),
    ));
  }

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoveryPSRXRoot(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

    if (!$supportsPSRX) {
      $this->assertTrue(TRUE);
      return;
    }

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $adapter->addNamespacePSRX('MyVendor\MyPackage', $dir);


    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $adapter->buildSearchableNamespaces(array(''))->apiVisitClassFiles($api, TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $dir . '/Foo/Bar.php',
          'MyVendor\MyPackage\Foo\Bar',
        ),
      ),
    ));
  }

  /**
   * @dataProvider getInspectors
   */
  public function testDiscoverExistingClasses(k\Adapter_NamespaceInspector_Interface $adapter, $supportsPSRX) {

    // Register PSR-0 and PSR-X mappings.
    $adapter->addNamespacePSR0('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0'));
    if ($supportsPSRX) {
      $adapter->addNamespacePSRX('MyVendor\MyPackage', $this->getFixturesSubdir('src-psrx'));
    }

    // Build SearchableNamespaces object.
    $namespaces = $adapter->buildSearchableNamespaces(array('Namespace_With_Underscore'));

    // Search.
    $classes = $namespaces->discoverExistingClasses(TRUE);
    $expected = array(
      'Namespace_With_Underscore\Sub_Namespace\Foo_Bar',
      'Namespace_With_Underscore\Sub_Namespace\Foo_BarUnsafe',
    );
    $this->assertArrayElements($classes, array_combine($expected, $expected));

    if ($supportsPSRX) {
      // Add another namespace (PSR-X)
      $namespaces->addNamespace('MyVendor');

      // Search again.
      $classes = $namespaces->discoverExistingClasses(TRUE);
      $expected[] = 'MyVendor\MyPackage\Foo\Bar';
      $this->assertArrayElements($classes, array_combine($expected, $expected));
    }
  }

  /**
   * @dataProvider getInspectors
   */
  public function testClassExistsInNamespace(k\Adapter_NamespaceInspector_Interface $adapter) {

    $adapter->addNamespacePSR0('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0'));
    $namespaces = $adapter->buildSearchableNamespaces();
    $this->assertFalse($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $namespaces->addNamespace('Namespace_With_Underscore\Sub_Namespace');
    $this->assertTrue($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $this->assertTrue($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $namespaces->addNamespace('MyVendor\MyPackage');
    $this->assertFalse($namespaces->classExistsInNamespaces('MyVendor\MyPackage\Foo\Bar'));
  }

  protected function assertArraySlice(array $array, $offset, $count, array $compare) {
    $slice = array_slice($array, $offset, $count);
    $this->assertArrayElements($slice, $compare);
  }

  protected function assertArrayElements(array $array, array $compare) {
    $this->sortBySerializing($array);
    $this->sortBySerializing($compare);
    $this->assertEquals($compare, $array);
  }

  protected function sortBySerializing(array &$array) {
    $sorted = array();
    foreach ($array as $item) {
      $sorted[] = serialize($item);
    }
    array_multisort($sorted, $array);
  }

  protected function getFixturesSubdir($suffix) {
    return 'tests/fixtures/' . $suffix;
  }

  public function getInspectors() {
    return array(
      array(k\Adapter_NamespaceInspector_Pluggable::start(), TRUE),
      array(k\Adapter_NamespaceInspector_Composer::start(), FALSE),
    );
  }

}
