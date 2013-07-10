<?php

namespace Krautoload\Tests;

use Krautoload as k;

class ClassDiscoveryTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var k\RegistrationHub
   */
  protected $hub;

  /**
   * @var k\NamespaceInspector_Interface
   */
  protected $finder;

  public function setUp() {
    $this->finder = new k\NamespaceInspector_Pluggable();
    $this->hub = new k\RegistrationHub($this->finder);
  }

  public function testDiscoveryPSR0() {

    // Register PSR-0 namespace.
    $psr0 = $this->getFixturesSubdir('src-psr0');
    $this->hub->addNamespacePSR0('Namespace_With_Underscore', $psr0);

    // Build the mock $api object.
    // We can't use the mock stuff shipped with PHPUnit, because we need a specific order of calls.
    $api = new k\InjectedAPI_ClassFileVisitor_Mock();

    // Run the discovery.
    $this->finder->apiVisitNamespaceClassFiles($api, 'Namespace_With_Underscore', TRUE);

    // Verify the result.
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('Namespace_With_Underscore')));

    $this->assertArraySlice($called, 1, 2, array(
      array(
        'fileWithClassCandidates',
        array(
          $psr0 . '/Namespace_With_Underscore/Sub_Namespace/Foo/BarUnsafe.php',
          array(
            '\Sub_Namespace\Foo\BarUnsafe',
            '\Sub_Namespace\Foo_BarUnsafe',
            '\Sub_Namespace_Foo_BarUnsafe',
          ),
        ),
      ),
      array(
        'fileWithClassCandidates',
        array(
          $psr0 . '/Namespace_With_Underscore/Sub_Namespace/Foo/Bar.php',
          array(
            '\Sub_Namespace\Foo\Bar',
            '\Sub_Namespace\Foo_Bar',
            '\Sub_Namespace_Foo_Bar',
          ),
        ),
      ),
    ));
  }

  public function testDiscoveryPSRX() {

    // Register PSR-X namespace.
    $psrx = $this->getFixturesSubdir('src-psrx');
    $this->hub->addNamespacePSRX('MyVendor\MyPackage', $psrx);

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->finder->apiVisitNamespaceClassFiles($api, 'MyVendor\MyPackage', TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor\MyPackage')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $psrx . '/Foo/Bar.php',
          '\Foo\Bar',
        ),
      ),
    ));

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->finder->apiVisitNamespaceClassFiles($api, 'MyVendor\MyPackage\Foo', TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor\MyPackage\Foo')));

    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $psrx . '/Foo/Bar.php',
          '\Bar',
        ),
      ),
    ));

    $api = new k\InjectedAPI_ClassFileVisitor_Mock();
    $this->finder->apiVisitNamespaceClassFiles($api, 'MyVendor', TRUE);
    $called = $api->mockGetCalled();

    $this->assertEquals($called[0], array('setNamespace', array('MyVendor')));

    /*
    $this->assertArraySlice($called, 1, 1, array(
      array(
        'fileWithClass',
        array(
          $psrx . '/Foo/Bar.php',
          '\MyPackage\Foo\Bar',
        ),
      ),
    ));
    */
  }

  protected function assertArraySlice($array, $offset, $count, $compare) {
    $slice = array_slice($array, $offset, $count);
    $this->sortBySerializing($slice);
    $this->sortBySerializing($compare);
    $this->assertEquals($slice, $compare);
  }

  protected function sortBySerializing(&$array) {
    $sorted = array();
    foreach ($array as $item) {
      $sorted[] = serialize($item);
    }
    array_multisort($sorted, $array);
  }

  protected function getFixturesSubdir($suffix) {
    return __DIR__ . '/../fixtures/' . $suffix;
  }
}
