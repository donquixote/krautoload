<?php

namespace Krautoload\Tests;

use Krautoload as k;

/**
 * @runTestsInSeparateProcesses
 */
class ClassDiscoveryTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var k\RegistrationHub
   */
  protected $hub;

  /**
   * @var k\NamespaceInspector_Interface
   */
  protected $inspector;

  public function setUp() {
    $this->inspector = new k\NamespaceInspector_Pluggable();
    $this->hub = new k\RegistrationHub($this->inspector);
  }

  public function testDiscoveryPSR0() {

    // Register PSR-0 namespace.
    $dir = $this->getFixturesSubdir('src-psr0');
    $this->hub->addNamespacePSR0('Namespace_With_Underscore', $dir);
    $this->hub->addNamespacePSR0('Namespaced', $dir);
    $this->hub->addNamespacePSR0('Namespaced2', $dir);

    // Build the mock $api object.
    // We can't use the mock stuff shipped with PHPUnit, because we need a specific order of calls.
    $api = new k\InjectedAPI_ClassFileVisitor_Mock();

    // Run the discovery.
    $this->hub->buildSearchableNamespaces(array(
      'Namespace_With_Underscore',
      'Namespaced',
      'Namespace_With_Underscore\Sub_Namespace',
    ))->apiVisitClassFiles($api, TRUE);

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

  public function testDiscoveryPSRX() {

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $dir);

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->hub->buildSearchableNamespaces(array('MyVendor\MyPackage'))->apiVisitClassFiles($api, TRUE);
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

  public function testDiscoveryPSRXChild() {

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $dir);

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->hub->buildSearchableNamespaces(array('MyVendor\MyPackage\Foo'))->apiVisitClassFiles($api, TRUE);
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

  public function testDiscoveryPSRXParent() {

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $dir);


    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->hub->buildSearchableNamespaces(array('MyVendor'))->apiVisitClassFiles($api, TRUE);
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

  public function testDiscoveryPSRXRoot() {

    // Register PSR-X namespace.
    $dir = $this->getFixturesSubdir('src-psrx');
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $dir);


    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->hub->buildSearchableNamespaces(array(''))->apiVisitClassFiles($api, TRUE);
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

  public function testClassExistsInNamespace() {

    $this->hub->addNamespacePSR0('Namespace_With_Underscore', $this->getFixturesSubdir('src-psr0'));
    $namespaces = $this->hub->buildSearchableNamespaces();
    $this->assertFalse($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $namespaces->addNamespace('Namespace_With_Underscore\Sub_Namespace');
    $this->assertTrue($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $this->assertTrue($namespaces->classExistsInNamespaces('Namespace_With_Underscore\Sub_Namespace\Foo_Bar'));
    $namespaces->addNamespace('MyVendor\MyPackage');
    $this->assertFalse($namespaces->classExistsInNamespaces('MyVendor\MyPackage\Foo\Bar'));

  }

  protected function assertArraySlice($array, $offset, $count, $compare) {
    $slice = array_slice($array, $offset, $count);
    $this->sortBySerializing($slice);
    $this->sortBySerializing($compare);
    $this->assertEquals($compare, $slice);
  }

  protected function sortBySerializing(&$array) {
    $sorted = array();
    foreach ($array as $item) {
      $sorted[] = serialize($item);
    }
    array_multisort($sorted, $array);
  }

  protected function getFixturesSubdir($suffix) {
    return 'tests/fixtures/' . $suffix;
  }
}
