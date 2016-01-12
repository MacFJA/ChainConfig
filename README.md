ChainConfig
===========

A configuration loader, where order matters.  


Features
--------

 * Multiple configuration paths
 * Multiple loader
   * Ini (`.ini`, `.properties`)
   * Json (`.json`)
   * Neon (`.neon`)
   * Php (`.php`)
   * Xml (`.xml`)
   * Yaml (`.yml`, `.yaml`)
 * Handle `.dist` file
 * Paths order matters
 * Readers order matters
 * Dynamic key
 * Dynamic value


### The `.dist` file handling

When the search for the config file to load, the library check if a `.dist` exist.
If it's the case, and there is no file without the `.dist` part, the file is used.

So, in this example:
```
 ├── test1.ini.dist
 ├── test2.ini
 └── test2.ini.dist
```
`test1.ini.dist` will be loaded,  
but not `test2.ini.dist` because the file `test2.ini` exist.
(and it's `test2.ini` that will be loaded)


### Order matters

Configuration data are loaded in the order you define.
Loader are used in the order you define.

Example:
```
├── 01-my-secondary-path/
│   └── a.php
└── 02-my-main-path/
    ├── a.ini
    ├── b.ini
    └── b.xml
```
If you define `02-my-main-path` to be the first path,
and `01-my-secondary-path` as the second path. If you ask of a `a.*` configuration then the library will first load `02-my-main-path/a.php` and after `02-my-main-path/a.ini`.
Later loading don't override already loaded data, later loading only add missing key/value.

The order of the reader also matter. In the previous structure, if you define the **Xml reader** before the **Ini loader**, then, if you ask a `b.*` config, the first file to be load is `02-my-main-path/b.xml` and after the `02-my-main-path/b.ini` file.


### Dynamics data

The library expose two callback functionality. One for the key, the other for the value.
Before the key is send to the library, it can be changed by one or more callback function.
Same thing for the value, just before returning it, the library will send the value to all registered callback.

This can be useful for change a key or a value depending on another configuration or an environment depends variable.
_See example below_


Installation
------------

The simplest way to install the library is to use [Composer](http://getcomposer.org):
``` sh
composer require macfja/chain-config
```


Usage
-----

Create a `Config` object:
``` php
$config = new Config();
$config->appendPath(__DIR__ . DIRECTORY_SEPARATOR . 'config');
$config->appendReader(new IniReader());
```

Read a configuration:
``` php
engine = $config->get('database.engine');
```

[Detailed class API](doc/Classes/Config.md)

[Example of usage](doc/Example.md)


Reader Format
-------------

[More information in Readers documentation](doc/Classes/Readers.md)


Limitation
----------

You can not use the directory separator (` / ` on Unix/Linux, ` \ ` on Windows) or the path separator (` : ` on Unix/Linux) in the first part of the key.


Additional Classes
------------------

You can found two class that **ChainConfig** use to work:

* [`MacFJA\ChainConfig\Collection\MultiPartKeyArray`](doc/Classes/MultiPartKeyArray.md): The class that power the reading/writing of dot key access (and the dot is just an option!)
* [`MacFJA\ChainConfig\Collection\OrderedGroup`](doc/Classes/OrderedGroup.md): The class that power the path grouping and ordering

