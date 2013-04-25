# Installation (use of composer.json is required)

## Install symfony 

Install and configure your symfony-standard if you have not done it yet [http://symfony.com/doc/master/book/installation.html]

## Install bundle with composer.json
Add to your composer.json in the repositories (create it if not exist) section:

``` json
        {
            "type": "vcs",
            "url": "git@github.com:muchomasfacil/InPageEditBundle.git"
        }
```

You must add this entries to your app/AppKernel.php
``` php
            new MuchoMasFacil\InPageEditBundle\MuchoMasFacilInPageEditBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
```

Add this routes in your app/config/routing.yml
``` yaml
# ...
fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"
    
ipe_routes:
    resource: "@MuchoMasFacilInPageEditBundle/Resources/config/routing.yml"
# ...    
```

Enable translation in your app/config/config.yml
``` yaml
# ...
framework:
# ...
    translator:      { fallback: %locale% }
# ...    
```

Finally run on your project (take care of "minimum-stability" if necesary)
``` bash
composer.phar require muchomasfacil/InPageEditBundle dev-master
```
Take a look at [composer.json](composer.json) to see the bundles InPageEditBundle relies on.

## (optional but recommended) Secure your InPageEditBundle
in your app/config/security.yml secure ipe_actions, for example add this:
``` yaml
#...
    firewall:
        # ... secure ip routes with http_basic
        ipe_secured_area:
            pattern:    ^/ipe
            anonymous: ~
            http_basic:
                realm: "IPE backend"
    role_hierarchy:
        # ... this add ROLE_IPE_EDITOR to, for example, ROLE_ADMIN
        ROLE_ADMIN:  [ROLE_USER, ROLE_IPE_EDITOR]
#...
```

## (optional) Overwrite or extend bundle configuration in your app/config/config.yml
Add an mucho_mas_facil_in_page_edit in your app/config.yml overwriting or extending existing definitions (PENDING PARAMS DESCRIPTION)
``` yaml
# ...
mucho_mas_facil_in_page_edit: 
    definitions:
        default:
        # ...
        my_definition:
            entity_class: MyBundle\Entity\MyEntity
        # ...
# ...
```

## (optional) if your want to use the bundle predefined helper entities 
Enable sortable doctrine extension in app/config/config.yml 
``` yaml
# in your app/config/config.yml
#...      
stof_doctrine_extensions:
    orm:
        default:
            sortable: true
# ...    
```

If you have not alredy done, configure your database params and then update your schema with your command line
``` bash
app/console doctrine:schema:create
```

### fast install of helper entities in a clean symfony-standard and using sqlite
If doing in a clean symfony-standard installation and :
``` yaml
# in your app/config/parameters.yml
# ...
    database_driver:   pdo_sqlite
    database_host:     ~
    database_port:     ~
    database_name:     ipe
    database_user:     user
    database_password: pass
# ...    
```

``` yaml
# in your app/config/config.yml
# ...
doctrine:
    dbal:
        driver:   %database_driver%
        host:     %database_host%
        port:     %database_port%
        dbname:   %database_name%
        user:     %database_user%
        password: %database_password%
        charset:  UTF8
        # if using pdo_sqlite as your database driver, add the path in parameters.yml
        # e.g. database_path: %kernel.root_dir%/data/data.db3
        # path:     %database_path%
        path:     %kernel.root_dir%/data/%database_name%.db
```

In the command line
``` bash
mkdir app/data
app/console doctrine:database:create
chmod a+w -R app/data # or adjust as needed
app/console doctrine:schema:create
```
