# in-page-edit-bundle
A Symfony2 bundle for fast (may be dirty but quite usefull; and ready for no high skilled users or clients) in page edition for general entities and common contents

**Work in progrees, testing its stability.**

## Installation (use of composer.json is required)

### Install symfony

Install and configure your symfony-standard if you have not done it yet [http://symfony.com/doc/master/book/installation.html]

### Changes in config files:

#### composer.json
Add to your composer.json in the repositories section:

```json
...
    "repositories": [
...
        {
            "type": "vcs",
            "url": "git://github.com/muchomasfacil/in-page-edit-bundle.git"
        },    
...
    ],
...
```
now run on your project (take care of "minimum-stability")
```bash
composer.phar require muchomasfacil/in-page-edit-bundle dev-master
```


#### changes in app/AppKernel.php
new MuchoMasFacil\InPageEditBundle\MuchoMasFacilInPageEditBundle(),
new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
