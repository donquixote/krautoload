<?php

namespace Krautoload;

interface NamespaceInspector_Interface extends ClassLoader_Interface {

  /**
   * @param InjectedAPI_NamespaceInspector_Interface $api
   * @param string $namespace
   */
  public function apiInspectNamespace($api, $namespace);


  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param string $namespace
   * @param boolean $recursive
   */
  function apiVisitNamespaceClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, $namespace, $recursive = FALSE);
}
