<?php

namespace Krautoload;

class InjectedAPI_ClassFileVisitor_CollectCandidateClasses extends InjectedAPI_ClassFileVisitor_Abstract {

  protected $classes = array();

  function getCollectedClasses() {
    return array_keys($this->classes);
  }

  function fileWithClass($file, $relativeClassName) {
    $this->classes[$this->getClassName($relativeClassName)] = TRUE;
  }

  function fileWithClassCandidates($file, $relativeClassNames) {
    foreach ($relativeClassNames as $relativeClassName) {
      $this->classes[$this->getClassName($relativeClassName)] = TRUE;
    }
  }
}
