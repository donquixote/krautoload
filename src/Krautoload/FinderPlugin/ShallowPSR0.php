<?php

namespace Krautoload;

class FinderPlugin_ShallowPSR0 implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($suffix, DIRECTORY_SEPARATOR)) {
      $suffix = substr($suffix, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($suffix, $pos));
    }
    else {
      $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    }

    // We "guess", because we don't know if the file exists.
    // It is a "candidate", because we don't know for sure if the file actually
    // declares the class we are looking for.
    if ($api->guessFileCandidate($dir . $suffix)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $prefix, $dir, $suffix) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($suffix, DIRECTORY_SEPARATOR)) {
      $suffix = substr($suffix, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($suffix, $pos));
    }
    else {
      $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    }

    // Check whether the file exists.
    if (is_file($file = $dir . $suffix)) {
      // We don't know if the file defines the class,
      // and whether it was already included.
      include_once $file;
      return class_exists($class);
    }
  }

  function pluginScanDirectory($api, $namespace, $dir) {
    foreach (new DirectoryIterator($dir) as $fileinfo) {
      // @todo Once core requires 5.3.6, use $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $class = $namespace . '\\' . $fileinfo->getBasename('.php');
        $api->fileWithClassCandidates($fileinfo->getPathname(), array($class));
      }
    }
  }

  function pluginScanRecursive($api, $namespace, $dir, $namespaceSuffixes = array('\\')) {
    foreach (new DirectoryIterator($dir) as $fileinfo) {
      // @todo Once core requires 5.3.6, use $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $classes = array();
        $suffixes = array();
        foreach ($namespaceSuffixes as $suffix) {
          $classes[] = $namespace . $suffix . $fileinfo->getBasename('.php');
          $suffixes[] = $suffix . $fileinfo->getBasename('.php');
        }
        if (!empty($classes)) {
          $api->fileWithClassCandidates($fileinfo->getPathname(), $classes, $namespace, $suffixes);
        }
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $childSuffixes = array();
        $childSuffixes[] = $namespaceSuffixes[0] . $fileinfo->getFilename() . '\\';
        foreach ($namespaceSuffixes as $suffix) {
          $childSuffixes[] = $suffix . $fileinfo->getFilename() . '_';
        }
        $this->pluginScanRecursive($api, $namespace, $fileinfo->getPathname(), $childSuffixes);
      }
    }
  }
}
