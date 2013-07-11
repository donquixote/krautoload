<?php

namespace Krautoload;

class SearchableNamespaces_Default implements SearchableNamespaces_Interface {

  protected $finder;
  protected $namespaces = array();

  /**
   * @param NamespaceInspector_Interface $finder
   */
  function __construct(NamespaceInspector_Interface $finder) {
    $this->finder = $finder;
  }

  /**
   * @param NamespaceInspector_Interface $finder
   */
  function setFinder(NamespaceInspector_Interface $finder) {
    $this->finder = $finder;
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
    $new = new self($this->finder);
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
    $namespaceVisitorAPI = $recursive
      ? new InjectedAPI_NamespaceInspector_ScanRecursive($api)
      : new InjectedAPI_NamespaceInspector_ScanNamespace($api)
    ;
    $this->apiInspectNamespaces($namespaceVisitorAPI, $recursive);
  }

  /**
   * @inheritdoc
   */
  function apiInspectNamespaces(InjectedAPI_NamespaceInspector_Interface $api, $recursive = FALSE) {
    $this->finder->apiInspectNamespaces($api, $this->namespaces, $recursive);
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
    return $this->finder->apiFindFile($api, $class);
  }
}
