Class MultiPartKeyArray
=======================


Features
--------

* Get a value by using a multi-part key
* Set a value by using a multi-part key
* Remove a value by using a multi-part key
* Transform a multi-dimension array into a one-dimension array with multi-part key

---

The class is covered at 100% with PHPUnit.

---

The class implements the following interfaces:
 - `\ArrayAccess` for access (read/write/isset/unset) like an array
 - `\IteratorAggregate` for `foreach` use
 - `\Countable` to get the number of values (usable with PHP `count()` function)
 - `\Serializable`


Multi-Part key? What hell is that?!
-----------------------------------

Considering the following array:
``` json
{
  "application": {
    "service": {
      "mail": {
        "unix": "sendmail"
      }
    }
  }
}
```
To get the value **sendmail** a traditional way is:

 - ask for the array `application`,
 - then ask for the sub array `service`,
 - then ask for the sub array `mail`,
 - and finally ask for the value of `unix`.

In PHP code i will look like:
``` php
$value = $array['application']['service']['mail']['unix']
```

The idea behind the Multi-part key, is to transform multi-level array call into just this:
``` php
$value = $array['application.service.mail.unix'];
```

### Q: Well you just save 9 chars...

Yeah :)
But I also have a unique identifier for the value.
It's also way easier to dynamically build a multi-part key than change the multi-level call.

It's way more easier to manipulate a one-dimension array, than play with recursion.


API
---

### Method `__construct([array $data[, string $keySeparator]])`

The class instance constructor.
The default value for `$data` is an empty array.
The default value for `$keySeparator` is "`.`"

The `$data` parameter is the initial array.

### Method `getKeySeparator()`

Get the multi-part key separator.

### Method `setKeySeparator(string $keySeparator)`

Set the multi-part key separator

### Method `::flattenArray(array $array[, string $prefix[, string $keySeparator]])`

**Static** method to flatten a multi-dimension array.
The Default value for `$prefix` is an empty string
The Default value for `$keySeparator` is "`.`"

The `$prefix` parameter allow you to add one (or more) key part(s) at the beginning of all multi-part key.

The flatten process will create a new array.
``` php
array(
    "application" => array(
        "service" => array(
            "mail" => array(
                "unix": "sendmail",
                "stmp": "127.0.0.1"
            )
        )
    )
);
// Flatten to (with default options)
array(
    "application.service.mail.unix" => "sendmail",
    "application.service.mail.smtp" => "127.0.0.1"
);
```

### Method `flatten()`

Flatten the current object according to the `keySeparator`.
The method return a new array.

See `::flattenArray` for more details.

### Method `::unFlattenArray(array $array[, string $keySeparator])`

**Static** method to un-flatten a multi-dimension array.
The Default value for `$keySeparator` is "`.`"

The un-flatten process will create a new multi-dimension array:
``` php
array(
    "database.mysql.host" = "127.0.0.1",
    "database.mysql.port" = 3306,
    "database.sqlite.file" = "db.sqlite",
    "cache.redis.host" = "localhost"
);
// Un-flatten to (with default keySeparator)
array(
    "database" => array(
        "mysql" => array(
            "host" => "127.0.0.1",
            "port" => 3306
        ),
        "sqlite" => array(
            "file" => "db.sqlite"
        )
    ),
    "cache" => array(
        "redis" => array(
            "host" => "localhost"
        )
    )
);
```

### Method `addArray(array $newData[, string $prefix[, bool $override]])`

Merge an array with the object values. The new value can override existing value if `$override` is set to `true`
The Default value for `$prefix` is an empty string
The Default value for `$override` is `false`

### Method `getValue(string $key)`

Get a value withe a multi-part key.

If the value does not exist, `null` will be return.

### Method `hasKey(string $key)`

Check if a multi-part key is defined.

### Method `addValue(string $key, mixed $value)`

Add a value.

### Method `removeValue(string $key)`

Remove a value.

Return `true` if the value is removed, `false` is the value does not exist.

### Method `toArray([bool $flatten])`

Return the array representation of the class.
The Default value for `$flatten` is `false`

If `$flatten` is set to `true` then the flatten version is return (see `flatten` and `::flattenArray` for mor details)