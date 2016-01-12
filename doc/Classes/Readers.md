Reader classes
==============


API Interface
-------------

### Method `canRead(string $filename)`

Indicate if the reader can read a file.

### Method `getExtensions()`

Get the list of supported file extension

### Method `read(string $filename)`

Reader a file.


Abstract class implementation
-----------------------------

The abstract implement the method `canRead`.

### Method `getContentWithLock(string $filename)`

Get the content of a file with file lock.
This prevent reading a file while an other process is writing it (The writting process need to use lock too).


Readers implementation
----------------------

 * `IniReader`, read **Ini** and **Properties** files (`.ini`, `.properties`)
 * `JsonReader`, read **Json** files (`.json`)
 * `NeonReader`, read **Neon** files (`.neon`)
 * `PhpReader`, read **PHP** files (`.php`)
 * `XmlReader`, read **XML** files (`.xml`)
 * `YamlReader`, read **YAML** files (`.yml`, `.yaml`)


Reader file format
------------------

### Ini and Properties

The Ini (`.ini`) format is the same than the one use in php.ini.
It support `[Section]` naming.
The Properties (`.properties`) format is the same as Ini format. the extension is used in Java application.

**Example, `database.ini`**
``` ini
[mysql]
host = localhost
username = root
password = root
port = 3306
dbname = app
[sqlite]
file = db.sqlite
```

### Json

The Json (`.json`) format.

**Example, `database.json`**
``` json
{
  "mysql": {
    "host": "localhost",
    "username": "root",
    "password": "root",
    "port": 3306,
    "dbname": "app"
  },
  "sqlite": {
    "file": "db.sqlite"
  }
}
```

### Neon

The Neon (`.neon`) format. The format was created by [Nette Framework](http://nette.org).
It's syntax is very similar to Yaml, but less complex.

**Example, `database.neon`**
``` neon
mysql:
  host: localhost
  username: root
  password: root
  port: 3306
  dbname: app
sqlite:
  file: db.sqlite
```
To use this format, you need to add `nette/neon` to your project

### Php

The Php (`.php`) format.
The file must return an array.

**Example, `database.php`**
``` php
<?php
return array(
    "mysql" => array(
        "host" => "localhost",
        "username" => "root",
        "password" => "root",
        "port" => 3306,
        "dbname" => "app"
    ),
    "sqlite" => array(
        "file" => "db.sqlite"
    )
);
```

### Xml

The Xml (`.xml`) format.

**Example, `database.xml`**
``` xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <mysql>
        <host>localhost</host>
        <username>root</username>
        <password>root</password>
        <port>3306</port>
        <dbname>app</dbname>
    </mysql>
    <sqlite>
        <file>db.sqlite</file>
    </sqlite>
</config>
```
The tag name of the first tag (`config` in the example) can be anything, but prefer `config` as it's have more meaning for the human reader.

### Yaml

The Yaml (`.yml`, `.yaml`) format

**Example, `database.yml`**
``` neon
mysql:
  host: localhost
  username: root
  password: root
  port: 3306
  dbname: app
sqlite:
  file: db.sqlite
```
To use this format, you need to add `symfony/yaml` to your project