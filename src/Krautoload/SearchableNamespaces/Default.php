<?php

namespace Krautoload;

class SearchableNamespaces_Default implements SearchableNamespaces_Interface {

  protected $inspector;
  protected $namespaces = array();

  /**
   * @param NamespaceInspector_Interface $inspector
   */
  function __construct(NamespaceInspector_Interface $inspector) {
    $this->inspector = $inspector;
  }

  /**
   * @param NamespaceInspector_Interface $inspector
   */
  function setFinder(NamespaceInspector_Interface $inspector) {
    $this->inspector = $inspector;
  }

  /**
   * @inheritdoc
   */
  function addNamespace($namespace) {
    $this->namespaces[$namespace] = $namespace;
  }

  /**
   * @inheritdoc
   */
  function setNamespaces(array $namespaces) {
    $this->namespaces = array();
    $this->addNamespaces($namespaces);
  }

  /**
   * @inheritdoc
   */
  function addNamespaces(array $namespaces) {
    foreach ($namespaces as $namespace) {
      $this->namespaces[$namespace] = $namespace;
    }
  }

  /**
   * @inheritdoc
   */
  function getNamespaces() {
    return $this->namespaces;
  }

  /**
   * @inheritdoc
   */
  function buildSearchableNamespaces(array $namespaces = array()) {
    $new = new self($this->inspector);
    $new->addNamespaces($namespaces);
    return $new;
  }

  /**
   * @inheritdoc
   */
  function buildFromSuffix($suffix) {
    if ('\\' !== $suffix[0]) {
      $suffix = '\\' . $suffix;
    }
    $new = $this->buildSearchableNamespaces();
    foreach ($this->namespaces as $namespace) {
      $new->addNamespace($namespace . $suffix);
    }
    return $new;
  }

  /**
   * @inheritdoc
   */
  function apiVisitClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, $recursive = FALSE) {
    $this->inspector->apiVisitClassFiles($api, $this->namespaces, $recursive);
  }

  /**
   * @inheritdoc
   */
  function apiInspectNamespaces(InjectedAPI_NamespaceInspector_Interface $api, $recursive = FALSE) {
    $this->inspector->apiInspectNamespaces($api, $this->namespaces, $recursive);
  }

  /**
   * @inheritdoc
   */
  function discoverExistingClasses($recursive = FALSE) {
    $api = new InjectedAPI_ClassFileVisitor_CollectExistingClasses();
    $this->apiVisitClassFiles($api, $recursive);
    return $api->getCollectedClasses();
  }

  /**
   * @inheritdoc
   */
  function discoverCandidateClasses($recursive = FALSE) {
    $api = new InjectedAPI_ClassFileVisitor_CollectCandidateClasses();
    $this->apiVisitClassFiles($api, $recursive);
    return $api->getCollectedClasses();
  }

  /**
   * @inheritdoc
   */
  function classExistsInNamespaces($class) {
    return $this->classIsInNamespaces($class) && $this->classExistsInFinder($class);
  }

  protected function classIsInNamespaces($class) {
    $prefix = $class;
    while (FALSE !== $pos = strrpos($prefix, '\\')) {
      $prefix = substr($prefix, 0, $pos);
      if (isset($this->namespaces[$prefix])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  protected function classExistsInFinder($class) {
    if (Util::classIsDefined($class)) {
      $api = new InjectedAPI_ClassFinder_FindExistingClass($class);
    }
    else {
      $api = new InjectedAPI_ClassFinder_LoadClass($class);
    }
    return $this->inspector->apiFindFile($api, $class);
  }
}
