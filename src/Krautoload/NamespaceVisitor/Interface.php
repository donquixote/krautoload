<?php

namespace Krautoload;

interface NamespaceVisitor_Interface {

  /**
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   * @param string $namespace
   */
  public function apiFindNamespace($api, $namespace);
}
