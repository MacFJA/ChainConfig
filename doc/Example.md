Read World example
==================

Let's imagine that you have an application, extended by 2 modules.


Directory Structure
-------------------
```
./
├── application.php
├── configs/
│   ├── production/
│   │   ├── application.yml
│   │   └── mysql.yml
│   ├── application.yml.dist
│   ├── sqlite.yml.dist
│   ├── mysql.yml.dist
│   └── mysql.yml
├── modules/
│   ├── module1/
│   │   ├── application.yml
│   │   └── def.php
│   └── module2/
│       ├── application.yml
│       ├── def.php
│       └── mysql.yml
└── vendor/
    └── ...

```

Your application configurations are stores in the **configs** directory and your production specific config in the directory **configs/production**.
Each module have their configurations in their main directory

Configurations files
--------------------

### configs/production/application.yml
``` yaml
database:
  engine: mysql
debug: false
cache: redis
```
### configs/production/mysql.yml
``` yaml
host: 192.168.0.1
username: mysql_user
password: TheProductionPassword
db_name: production
```
### configs/production/application.yml.dist
``` yaml
database:
  engine: sqlite
debug: true
cache: files
```
### configs/sqlite.yml.dist
``` yaml
path: %PROJECT%/db.sqlite
```
### configs/mysql.yml.dist
``` yaml
host: localhost
username: root
password: root
db_name: application_skeleton
port: 3306
```
### configs/mysql.yml
``` yaml
host: localhost
username: root
password: rootroot
db_name: my_application
port: 3306
```

### modules/module1/application.yml
``` yaml
cache: apc
```

### modules/module2/application.yml
``` yaml
cache_version: apcu
```

### modules/module2/mysql.yml
``` yaml
db_name: my_app
```

Application
-----------

### application.php
``` php
require 'vendor/autoload.php';
define('PROJECT_ROOT', __DIR__);
define('ENV', 'production');
$config = new \MacFJA\ChainConfig\Config();
$config->appendPath(__DIR__ . DIRECTORY_SEPARATOR . 'configs');
$config->appendReader(new \MacFJA\ChainConfig\Reader\YamlReader());

// ...

$config->prependPath(__DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . ENV);
$config->appendKeyCallback(function(key) use ($config) {
    if (str_pos('{database_engine}', $key) !== false) {
        return str_replace('{database_engine}', $config->get('application.database.engine'), $key);
    }
    return $key;
});
$config->appendValueCallback(function(key, $value) {
    return str_replace('%PROJECT%', PROJECT_ROOT, $value);
});

// ...
// Load your module
// ...

$databaseLayer = new YourDatabaseAbstractionLayer($config->get('{database_engine}'));
```
### modules/module1/def.php
``` php
$config->appendPath(__DIR__, 'module1');
$config->setGroupPosition('module1', \MacFJA\ChainConfig\Collection\OrderedGroup::DEFAULT_GROUP);
```

### modules/module2/def.php
``` php
$config->appendPath(__DIR__, 'module2');
$config->setGroupPosition('module1',  \MacFJA\ChainConfig\Collection\OrderedGroup::DEFAULT_GROUP, 'module1');
```

Configuration result
--------------------

``` yml
application:
  database:
    engine: mysql
  debug: false
  cache: apc
  cache_version: apcu
mysql:
  host: 192.168.0.1
  username: mysql_user
  password: TheProductionPassword
  db_name: my_app
  port: 3306
```