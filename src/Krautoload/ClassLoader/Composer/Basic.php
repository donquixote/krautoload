<?php

namespace Krautoload;


class ClassLoader_Composer_Basic extends ClassLoader_Composer_Abstract {

  /**
   * @var array
   */
  protected $prefixes = array();

  /**
   * @var array
   */
  protected $fallbackDirs = array();

  /**
   * @inheritdoc
   */
  public function add($prefix, $paths, $prepend = false) {
    if (!$prefix) {
      if ($prepend) {
        $this->fallbackDirs = array_merge(
          (array) $paths,
          $this->fallbackDirs
        );
      } else {
        $this->fallbackDirs = array_merge(
          $this->fallbackDirs,
          (array) $paths
        );
      }

      return;
    }

    $first = $prefix[0];
    if (!isset($this->prefixes[$first][$prefix])) {
      $this->prefixes[$first][$prefix] = (array) $paths;

      return;
    }
    if ($prepend) {
      $this->prefixes[$first][$prefix] = array_merge(
        (array) $paths,
        $this->prefixes[$first][$prefix]
      );
    } else {
      $this->prefixes[$first][$prefix] = array_merge(
        $this->prefixes[$first][$prefix],
        (array) $paths
      );
    }
  }

  /**
   * @inheritdoc
   */
  public function findFile($class) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      return $this->classMap[$class];
    }

    if (false !== $pos = strrpos($class, '\\')) {
      // namespaced class name
      $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    } else {
      // PEAR-like class name
      $classPath = null;
      $className = $class;
    }

    $classPath .= strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

    $first = $class[0];
    if (isset($this->prefixes[$first])) {
      foreach ($this->prefixes[$first] as $prefix => $dirs) {
        if (0 === strpos($class, $prefix)) {
          foreach ($dirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
              return $dir . DIRECTORY_SEPARATOR . $classPath;
            }
          }
        }
      }
    }

    foreach ($this->fallbackDirs as $dir) {
      if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
        return $dir . DIRECTORY_SEPARATOR . $classPath;
      }
    }

    if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
      return $file;
    }

    return $this->classMap[$class] = false;
  }

  /**
   * @inheritdoc
   */
  public function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      if ($file = $this->classMap[$class]) {
        $api->claimFile($file);
        return TRUE;
      }
      else {
        return FALSE;
      }
    }

    if (false !== $pos = strrpos($class, '\\')) {
      // namespaced class name
      $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    } else {
      // PEAR-like class name
      $classPath = null;
      $className = $class;
    }

    $classPath .= strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

    $first = $class[0];
    if (isset($this->prefixes[$first])) {
      foreach ($this->prefixes[$first] as $prefix => $dirs) {
        if (0 === strpos($class, $prefix)) {
          foreach ($dirs as $dir) {
            if ($api->guessFile($dir . DIRECTORY_SEPARATOR . $classPath)) {
              return TRUE;
            }
          }
        }
      }
    }

    foreach ($this->fallbackDirs as $dir) {
      if ($api->guessFile($dir . DIRECTORY_SEPARATOR . $classPath)) {
        return TRUE;
      }
    }

    if ($this->useIncludePath && $api->guessFile_checkIncludePath($classPath)) {
      return TRUE;
    }

    return $this->classMap[$class] = FALSE;
  }

}