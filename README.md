# router
Router package for Bone Mvc Framework
## installation
Use Composer
```
composer require delboy1978uk/bone-router
```
## usage
Simply add to the `config/packages.php`
```php
<?php

// use statements here
use Bone\Router\RouterPackage;

return [
    'packages' => [
        // packages here...,
        RouterPackage::class,
    ],
    // ...
];
```