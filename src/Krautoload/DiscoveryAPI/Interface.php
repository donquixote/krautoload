<?php

namespace Krautoload;

interface DiscoveryAPI_Interface {

  function fileWithClass($file, $class);

  function fileWithClassCandidates($file, $classes);
}
