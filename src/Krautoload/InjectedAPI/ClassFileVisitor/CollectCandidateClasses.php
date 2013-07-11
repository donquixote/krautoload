<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_CollectCandidateClasses extends InjectedAPI_ClassFileVisitor_Abstract {

  protected $classes = array();

  /**
   * @return array
   *   Collected classes.
   */
  function getCollectedClasses() {
    return array_keys($this->classes);
  }

  /**
   * @inheritdoc
   */
  function fileWithClass($file, $relativeClassName) {
    $this->classes[$this->getClassName($relativeClassName)] = TRUE;
  }

  /**
   * @inheritdoc
   */
  function fileWithClassCandidates($file, array $relativeClassNames) {
    foreach ($relativeClassNames as $relativeClassName) {
      $this->classes[$this->getClassName($relativeClassName)] = TRUE;
    }
  }
}
