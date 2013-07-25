<?php

namespace Krautoload;


class ClassLoader_ComposerPSR4_SmartPredictor extends ClassLoader_Composer_Abstract implements ClassLoader_ComposerPSR4_Interface {

  private $prefixLengths = array();
  private $prefixDirs = array();
  protected $fallbackDirs = array();

  protected $predictorIndex = 8;

  const PSR0 = 1;
  const PSR4 = 2;

  /**
   * @param int $predictorIndex
   */
  public function setPredictorIndex($predictorIndex) {
    $this->predictorIndex = $predictorIndex;
  }

  /**
   * @inheritdoc
   */
  public function add($prefix, $paths, $prepend = FALSE) {
    $paths = is_array($paths) ? array_fill_keys($paths, self::PSR0) : array($paths => self::PSR0);
    $this->addPrefixPaths($prefix, $paths, $prepend);
  }

  /**
   * @inheritdoc
   */
  public function addNamespacesPSR4(array $namespaces, $prepend = FALSE) {
    foreach ($namespaces as $namespace => $paths) {
      $this->addNamespacePSR4($namespace, $paths, $prepend);
    }
  }

  /**
   * @inheritdoc
   */
  public function addNamespacePSR4($namespace, $paths, $prepend = FALSE) {
    $namespace = trim($namespace, '\\');
    $namespace = strlen($namespace) ? $namespace . '\\' : '';
    $paths = is_array($paths) ? array_fill_keys($paths, self::PSR4) : array($paths => self::PSR4);
    $this->addPrefixPaths($namespace, $paths, $prepend);
  }

  protected function addPrefixPaths($prefix, array $paths, $prepend) {
    if (!$prefix) {
      if ($prepend) {
        $this->fallbackDirs = array_merge(
          (array) $paths,
          $this->fallbackDirs
        );
      }
      else {
        $this->fallbackDirs = array_merge(
          $this->fallbackDirs,
          (array) $paths
        );
      }

      return;
    }

    if (!isset($this->prefixDirs[$prefix])) {
      $predictor = $prefix[0];
      if (isset($prefix[$this->predictorIndex])) {
        $predictor .= $prefix[$this->predictorIndex];
      }
      $this->prefixLengths[$predictor][$prefix] = strlen($prefix);
      $this->prefixDirs[$prefix] = $paths;
    }
    elseif ($prepend) {
      $this->prefixDirs[$prefix] += $paths;
    }
    else {
      $this->prefixDirs[$prefix] = $paths + $this->prefixDirs[$prefix];
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
      if (!$this->classMap[$class]) {
        return FALSE;
      }
      else {
        require $this->classMap[$class];
        return TRUE;
      }
    }

    if (false !== $pos = strrpos($class, '\\')) {
      // namespaced class name
      $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      // $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    }
    else {
      // PEAR-like class name
      $namespacePath = '';
      // $classPath = null;
      $className = $class;
    }

    $classNamePathPSR0 = strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

    $first = $class[0];
    foreach (isset($class[$this->predictorIndex])
      ? array($first . $class[$this->predictorIndex], $first)
      : array($first)
      as $predictor
    ) {
      if (isset($this->prefixLengths[$predictor])) {
        foreach ($this->prefixLengths[$predictor] as $prefix => $length) {
          if (0 === strpos($class, $prefix)) {
            foreach ($this->prefixDirs[$prefix] as $dir => $type) {
              if (self::PSR0 === $type) {
                if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $namespacePath . $classNamePathPSR0)) {
                  if ($returnFile) {
                    return $file;
                  }
                  require $file;
                  return TRUE;
                }
              }
              else {
                // PSR-4.
                if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($namespacePath, $length) . $className . '.php')) {
                  if ($returnFile) {
                    return $file;
                  }
                  require $file;
                  return TRUE;
                }
              }
            }
          }
        }
      }
    }

    $classPathPSR0 = $namespacePath . $classNamePathPSR0;

    foreach ($this->fallbackDirs as $dir) {
      if (file_exists($dir . DIRECTORY_SEPARATOR . $classPathPSR0)) {
        if ($returnFile) {
          return $dir . DIRECTORY_SEPARATOR . $classPathPSR0;
        }
        require $dir . DIRECTORY_SEPARATOR . $classPathPSR0;
        return TRUE;
      }
    }

    if ($this->useIncludePath && $file = stream_resolve_include_path($classPathPSR0)) {
      if ($returnFile) {
        return $file;
      }
      require $file;
      return TRUE;
    }

    return $this->classMap[$class] = FALSE;
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
      $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos + 1));
      // $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    }
    else {
      // PEAR-like class name
      $namespacePath = '';
      // $classPath = null;
      $className = $class;
    }

    $classNamePathPSR0 = strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

    $first = $class[0];
    foreach (isset($class[$this->predictorIndex])
       ? array($first . $class[$this->predictorIndex], $first)
       : array($first)
       as $predictor
    ) {
      if (isset($this->prefixLengths[$predictor])) {
        foreach ($this->prefixLengths[$predictor] as $prefix => $length) {
          if (0 === strpos($class, $prefix)) {
            foreach ($this->prefixDirs[$prefix] as $dir => $type) {
              if (self::PSR0 === $type) {
                if ($api->guessFile($dir . DIRECTORY_SEPARATOR . $namespacePath . $classNamePathPSR0)) {
                  return TRUE;
                }
              }
              else {
                // PSR-4.
                if ($api->guessFile($dir . DIRECTORY_SEPARATOR . substr($namespacePath, $length) . $className . '.php')) {
                  return TRUE;
                }
              }
            }
          }
        }
      }
    }

    $classPathPSR0 = $namespacePath . $classNamePathPSR0;

    foreach ($this->fallbackDirs as $dir) {
      if ($api->guessFile($dir . DIRECTORY_SEPARATOR . $classPathPSR0)) {
        return TRUE;
      }
    }

    if ($this->useIncludePath && $api->guessFile_checkIncludePath($classPathPSR0)) {
      return TRUE;
    }

    return $this->classMap[$class] = FALSE;
  }
}