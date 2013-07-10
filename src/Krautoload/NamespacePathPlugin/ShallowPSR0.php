<?php

namespace Krautoload;

class NamespacePathPlugin_ShallowPSR0 implements NamespacePathPlugin_Interface {

  function pluginFindFile($api, $baseDir, $relativePath) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }

    // We "guess", because we don't know if the file exists.
    // It is a "candidate", because we don't know for sure if the file actually
    // declares the class we are looking for.
    if ($api->guessFileCandidate($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }

    // Check whether the file exists.
    if (is_file($file = $baseDir . $relativePath)) {
      // We don't know if the file defines the class,
      // and whether it was already included.
      include_once $file;
      return class_exists($class, FALSE)
        || interface_exists($class, FALSE)
        || (function_exists('trait_exists') && trait_exists($class, FALSE))
      ;
    }
  }

  function pluginScanNamespace($api, $baseDir, $relativePath) {
    if (is_dir($dir = $baseDir . $relativePath)) {
      foreach (new \DirectoryIterator($dir) as $fileinfo) {
        // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
        if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
          $api->fileWithClassCandidates($fileinfo->getPathname(), array('\\' . $fileinfo->getBasename('.php')));
        }
      }
    }
  }

  function pluginScanRecursive($api, $baseDir, $relativePath) {
    if (is_array($baseDir)) {
      throw new \Exception("Base dir must not be array.");
    }
    if (is_array($relativePath)) {
      throw new \Exception("Relative path must not be array.");
    }
    if (is_dir($dir = $baseDir . $relativePath)) {
      $this->doScanRecursive($api, $dir);
    }
  }

  protected function doScanRecursive($api, $dir, $relativeNamespaces = array('\\')) {
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $relativeClassNames = array();
        foreach ($relativeNamespaces as $relativeNamespace) {
          $relativeClassNames[] = $relativeNamespace . $fileinfo->getBasename('.php');
        }
        if (!empty($relativeClassNames)) {
          $api->fileWithClassCandidates($fileinfo->getPathname(), $relativeClassNames);
        }
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $relativeSubNamespaces = array($relativeNamespaces[0] . $fileinfo->getFilename() . '\\');
        foreach ($relativeNamespaces as $relativeNamespace) {
          $relativeSubNamespaces[] = $relativeNamespace . $fileinfo->getFilename() . '_';
        }
        $this->doScanRecursive($api, $fileinfo->getPathname(), $relativeSubNamespaces);
      }
    }
  }
}
