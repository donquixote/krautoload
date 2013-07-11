<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_Mock extends InjectedAPI_ClassFileVisitor_Abstract {

  protected $called = array();

  /**
   * @return array
   *   The logged method calls.
   */
  function mockGetCalled() {
    return $this->called;
  }

  /**
   * @inheritdoc
   */
  function setNamespace($namespace) {
    $this->called[] = array(__FUNCTION__, func_get_args());
    parent::setNamespace($namespace);
  }

  /**
   * @inheritdoc
   */
  function fileWithClass($file, $relativeClassName) {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }

  /**
   * @inheritdoc
   */
  function fileWithClassCandidates($file, array $relativeClassNames) {
    $this->called[] = array(__FUNCTION__, func_get_args());
  }
}