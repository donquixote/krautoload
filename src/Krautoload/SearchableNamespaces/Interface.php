<?php

namespace Krautoload;

interface SearchableNamespaces_Interface {

  /**
   * Add a namespace to the family.
   *
   * @param string $namespace
   */
  function addNamespace($namespace);

  /**
   * Add namespaces to the family.
   *
   * @param array $namespaces
   */
  function addNamespaces(array $namespaces);

  /**
   * Get namespaces.
   *
   * @param array $namespaces
   */
  function getNamespaces();

  /**
   * @param array $namespaces
   *   Namespaces for the new family.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildSearchableNamespaces(array $namespaces = array());

  /**
   * @param string $suffix
   *   Namespace suffix to append to each namespace.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildFromSuffix($suffix);

  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $namespaces
   */
  function apiVisitClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, $recursive = FALSE);

  /**
   * Visit all namespaces.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   */
  function apiVisitNamespaces(InjectedAPI_NamespaceVisitor_Interface $api);

  /**
   * Scan all registered namespaces for class files, include each file, and
   * return all classes that actually exist (but no interfaces).
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   *
   * @return array
   *   Collected class names.
   */
  function discoverExistingClasses($recursive = FALSE);

  /**
   * Scan all registered namespaces for class files, and return all names that
   * may be defined as a class or interface within these namespaces.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   *
   * @return array
   *   Collected class names.
   */
  function discoverCandidateClasses($recursive = FALSE);

  /**
   * Check if the given class is "known", and load it.
   * This will check the following:
   * - Is the class within any of the registered namespaces?
   * - Is there is a file for this class, within the registered directories?
   *   (Include that file, if it exists.)
   * - Is the class defined after file inclusion?
   *
   * The method can return FALSE even if the class is defined
   */
  function classExistsInNamespaces($class);
}
