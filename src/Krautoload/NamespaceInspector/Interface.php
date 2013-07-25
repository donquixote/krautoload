<?php

namespace Krautoload;

interface NamespaceInspector_Interface extends ClassLoader_Interface {

  /**
   * @param InjectedAPI_NamespaceInspector_Interface $api
   * @param array $namespaces
   * @param bool $recursive
   */
  public function apiInspectNamespaces(InjectedAPI_NamespaceInspector_Interface $api, array $namespaces, $recursive);

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $namespaces
   * @param $recursive
   */
  public function apiVisitClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, array $namespaces, $recursive);
}
