<?php

namespace Krautoload;

class Adapter_NamespaceInspector_Pluggable extends Adapter_ClassLoader_Pluggable implements Adapter_NamespaceInspector_Interface {

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
    $inspector = new NamespaceInspector_Pluggable();
    return new self($inspector);
  }

  /**
   * @param NamespaceInspector_Pluggable_Interface $finder
   *   A finder object where namespace and prefix plugins can be registered.
   */
  function __construct(NamespaceInspector_Pluggable_Interface $finder) {
    parent::__construct($finder);
  }

  /**
   * @inheritdoc
   */
  function getInspector() {
    return $this->finder;
  }

  /**
   * @param array $namespaces
   * @return SearchableNamespaces_Interface
   * @throws \Exception
   */
  function buildSearchableNamespaces(array $namespaces = array()) {
    $searchable = new SearchableNamespaces_Default($this->finder);
    $searchable->addNamespaces($namespaces);
    return $searchable;
  }
}
