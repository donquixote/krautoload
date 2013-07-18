<?php

namespace Krautoload;

/**
 * Class RegistrationHub
 * @package Krautoload
 * Legacy alias of Adapter_NamespaceInspector_Pluggable
 */
class RegistrationHub extends Adapter_NamespaceInspector_Pluggable {

  /**
   * @var ClassLoader_Pluggable_Interface
   */
  protected $finder;

  /**
   * @param ClassLoader_Pluggable_Interface $finder
   *   A finder object where namespace and prefix plugins can be registered.
   */
  function __construct(ClassLoader_Pluggable_Interface $finder) {
    Adapter_ClassLoader_Pluggable::__construct($finder);
  }

  /**
   * @param array $namespaces
   * @return SearchableNamespaces_Interface
   * @throws \Exception
   */
  function buildSearchableNamespaces(array $namespaces = array()) {
    if (!$this->finder instanceof NamespaceInspector_Interface) {
      throw new \Exception("Introspection not possible with the given class loader object.");
    }
    return parent::buildSearchableNamespaces($namespaces);
  }
}
