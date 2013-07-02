<?php

namespace Krautoload;

/**
 * This thing can find files for classes, but it uses a funky signature, which
 * makes it different from other class finders you may have seen elsewhere.
 *
 * The main class finding method is apiFindFile(), which does not return a file
 * or include it directly, but instead sends the result to the $api object
 * passed into the method as a parameter.
 *
 * The benefit is that all filesystem contact can be mocked out, by passing in
 * a different implementation for the $api argument.
 */
class ApiClassFinder extends ApiClassFinder_Pluggable {}
