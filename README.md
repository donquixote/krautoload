Krautoload is a pluggable PHP class autoloader library that makes you think of Kartoffelbrei, Kasseler and Sauerkraut.  
It has native support for
- PSR-0
- Variation of PSR-0 which allows shallow directory structures.
- PEAR
- Variation of PEAR which allows shallow directory structures.
- The proposed PSR-X, which is a shallow-dir variation of PSR-0 without the special underscore handling.

Besides that, custom plugins can be mapped to any namespaces and prefixes.  
This way, you can easily support old-school libraries which don't support any standards, without bloating the SPL autoload stack.

Krautoload is designed for high performance, especially in the case of large namespace maps, as often found in modular architectures.


## Project status and history

The project is to be considered in "Preview" status.  
It should work ok, but API details may still be changed based on community feedback.

Cache solutions (APC) exist but are not accessible yet.

The project is a spin-off of the ["xautoload" module for Drupal](http://drupal.org/project/xautoload), with some changes.  

Unlike xautoload, Krautoload is written in anticipation of the hopefully upcoming PSR-X.  
It is optimized for PSR-X, and needs a tiny-tiny extra operation if wired up with PSR-0.


## Usage

Krautoload provides a start-off class with static methods, for those who want to avoid a lengthy bootstrap.  
Alternative bootstrap helpers may be provided based on your feedback.

```php
require_once "$path_to_krautoload/src/Krautoload.php";

// Create the class loader and register it.
Krautoload::start();
// Register additional namespaces
Krautoload::registration()->namespacePSR0('FooVendor\FooPackage', "$path_to_foo_package/src");

new FooVendor\FooPackage\Foo\Bar\Baz();
```

See [Krautoload\RegistrationHub](https://github.com/donquixote/krautoload/blob/master/src/Krautoload/RegistrationHub.php)
to see all the available registration methods.


## Unit tests

Krautoload is designed to be unit-testable, better than other class loaders.  
Its architecture allows to mock out and simulate all hits to the filesystem (file_exists(), require_once, etc).

Unfortunately, I have no experience with testing frameworks outside of Drupal (yet).  
Thus, no tests exist yet.  
(but there are tests in Drupal xautoload, which show that the architecture is ok)
