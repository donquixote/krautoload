<?php

namespace Krautoload;

interface Adapter_NamespaceInspector_Interface extends Adapter_ClassLoader_Interface {

  /**
   * Get the namespace inspector object.
   *
   * @return NamespaceInspector_Pluggable_Interface
   *   The namespace inspector object.
   *   Usually this is identical with the object returned by getFinder(), just
   *   with a different docblock type hint.
   */
  function getInspector();

  /**
   * @param array $namespaces
   * @return SearchableNamespaces_Interface
   */
  function buildSearchableNamespaces(array $namespaces = array());
}
