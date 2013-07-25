<?php

namespace Krautoload;


class ClassLoader_Composer_SmartPredictor extends ClassLoader_Composer_Abstract {

  protected $prefixes = array();
  protected $fallbackDirs = array();
  protected $predictorIndex = 8;

  /**
   * Registers a set of classes, merging with any others previously set.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $paths
   *   The location(s) of the classes
   * @param bool $prepend
   *   Prepend the location(s)
   */
  public function add($prefix, $paths, $prepend = false)
  {
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

    $predictor = $prefix[0] . (isset($prefix[$this->predictorIndex]) ? $prefix[$this->predictorIndex] : '');
    if (!isset($this->prefixes[$predictor][$prefix])) {
      $this->prefixes[$predictor][$prefix] = (array) $paths;

      return;
    }
    if ($prepend) {
      $this->prefixes[$predictor][$prefix] = array_merge(
        (array) $paths,
        $this->prefixes[$predictor][$prefix]
      );
    } else {
      $this->prefixes[$predictor][$prefix] = array_merge(
        $this->prefixes[$predictor][$prefix],
        (array) $paths
      );
    }
  }

  /**
   * @inheritdoc
   */
  public function loadClass($class, $returnFile = FALSE) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      if ($returnFile) {
        return $this->classMap[$class];
      }
      elseif ($this->classMap[$class]) {
        require $this->classMap[$class];
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
    foreach (isset($class[$this->predictorIndex])
               ? array($first . $class[$this->predictorIndex], $first)
               : array($first)
             as $predictor
    ) {
      if (isset($this->prefixes[$predictor])) {
        foreach ($this->prefixes[$predictor] as $prefix => $dirs) {
          if (0 === strpos($class, $prefix)) {
            foreach ($dirs as $dir) {
              if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                if ($returnFile) {
                  return $dir . DIRECTORY_SEPARATOR . $classPath;
                }
                require $dir . DIRECTORY_SEPARATOR . $classPath;
                return TRUE;
              }
            }
          }
        }
      }
    }

    foreach ($this->fallbackDirs as $dir) {
      if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
        if ($returnFile) {
          return $dir . DIRECTORY_SEPARATOR . $classPath;
        }
        require $dir . DIRECTORY_SEPARATOR . $classPath;
        return TRUE;
      }
    }

    if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
      if ($returnFile) {
        return $file;
      }
      require $file;
      return TRUE;
    }

    return $this->classMap[$class] = false;
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param string $class The name of the class
   *
   * @return string|bool The path if found, false otherwise
   */
  public function findFile($class) {
    return $this->loadClass($class, TRUE);
  }

  public function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      if (!$this->classMap[$class]) {
        return FALSE;
      }
      else {
        $api->claimFile($this->classMap[$class]);
        return TRUE;
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
    foreach (isset($class[$this->predictorIndex])
               ? array($first . $class[$this->predictorIndex], $first)
               : array($first)
             as $predictor
    ) {
      if (isset($this->prefixes[$predictor])) {
        foreach ($this->prefixes[$predictor] as $prefix => $dirs) {
          if (0 === strpos($class, $prefix)) {
            foreach ($dirs as $dir) {
              if ($api->guessFile($dir . DIRECTORY_SEPARATOR . $classPath)) {
                return TRUE;
              }
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

    return $this->classMap[$class] = false;
  }
}