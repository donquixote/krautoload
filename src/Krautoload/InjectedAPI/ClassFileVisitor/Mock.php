<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_Mock implements InjectedAPI_ClassFileVisitor_Interface {

  protected $called = array();

  function mockGetCalled() {
    return $this->called;
  }

  function setNamespace($namespace) {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }

  function getNamespace() {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }

  function fileWithClass($file, $relativeClassName) {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }

  function fileWithClassCandidates($file, $relativeClassNames) {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }
}