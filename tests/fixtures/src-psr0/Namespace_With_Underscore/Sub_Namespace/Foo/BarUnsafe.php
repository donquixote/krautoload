<?php

namespace Namespace_With_Underscore\Sub_Namespace;

if (class_exists('Namespace_With_Underscore\Sub_Namespace\Foo_BarUnsafe', FALSE)) {
  throw new \Exception('Cannot redefine class.');
}
else {
  eval('namespace Namespace_With_Underscore\Sub_Namespace; class Foo_BarUnsafe {}');
}
