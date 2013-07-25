<?php

namespace Krautoload;

class Adapter_NamespaceInspector_Composer extends Adapter_ClassLoader_Composer implements Adapter_NamespaceInspector_Interface {

  /**
   * @var NamespaceInspector_Pluggable_Interface
   */
  protected $finder;

  /**
   * Construct an empty inspector, and wrap it into a new adapter.
   *
   * @return self
   */
  static function start() {
    $inspector = new NamespaceInspector_Composer_Basic();
    return new self($inspector);
  }

  /**
   * @param NamespaceInspector_Composer_Interface $inspector
   *   A finder object where namespace and prefix plugins can be registered.
   */
  function __construct(NamespaceInspector_Composer_Interface $inspector) {
    parent::__construct($inspector);
  }

  /**
   * @return NamespaceInspector_Composer_Interface
   */
  function getInspector() {
    return $this->finder;
  }

  /**
   * @param array $namespaces
   * @return SearchableNamespaces_Interface
   */
  function buildSearchableNamespaces(array $namespaces = array()) {
    $searchable = new SearchableNamespaces_Default($this->finder);
    $searchable->addNamespaces($namespaces);
    return $searchable;
  }

}
