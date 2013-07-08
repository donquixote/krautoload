<?php

namespace Krautoload;

interface InjectedAPI_ClassFileVisitor_Interface {

  function setNamespace($namespace);

  function getNamespace();

  function fileWithClass($file, $relativeClassName);

  function fileWithClassCandidates($file, $relativeClassNames);
}
