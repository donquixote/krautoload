<?php

namespace Krautoload;

class FixtureGenerator extends RegistrationHub {

  function __construct() {
    $this->resetFinder();
  }

  function resetFinder() {
    $this->finder = new ClassFinder_Pluggable();
  }

  function generateClassFile($class) {

    $api = new \Krautoload\InjectedAPI_ClassFinder_FindGeneratorDestination($class);
    $this->finder->apiFindFile($api, $class);
    $path = $api->getDestination();

    if (FALSE !== $pos = strrpos($class, '\\')) {
      $namespace = substr($class, 0, $pos);
      $className = substr($class, $pos + 1);
    }
    $php = <<<EOT
namespace $namespace;
class $class {}
EOT;

    $this->generateParentDir($path);
    file_put_contents($path, $php);
  }

  protected function generateParentDir($path) {
    if (FALSE !== $pos = strrpos($path, '/')) {
      $parent = substr($path, 0, $pos);
      if (is_dir($parent)) {
        return TRUE;
      }
      $this->generateParentDir($parent);
      mkdir($parent);
    }
  }

}
