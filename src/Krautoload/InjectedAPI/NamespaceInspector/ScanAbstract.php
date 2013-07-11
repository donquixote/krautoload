<?php

namespace Krautoload;

abstract class InjectedAPI_NamespaceInspector_ScanAbstract implements InjectedAPI_NamespaceInspector_Interface {

  protected $api;

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   */
  function __construct($api) {
    $this->api = $api;
  }

  /**
   * @inheritdoc
   */
  function setNamespace($namespace) {
    $this->api->setNamespace($namespace);
  }
}
