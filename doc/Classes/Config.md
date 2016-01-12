Class Config
============


API
---

### Method `__construct([array $data])`

The class instance constructor.
The default value for `$data` is an empty array.

The `$data` parameter is the initial array.

### Method `get(string $key[, mixed $default[, bool $setIfDefault]])`

Get a config value.
The default value for `$default` is `null`.
The default value for `$setIfDefault` is `false`.

If the multi-part key `$key` does exist, and `$setIfDefault` is `true` then the default value will be save in the config object.
On the next call, it's the value of `$default` that will be returned.

### Method `getCurrentConfigurations()`

Return all loaded value of the config. (can be useful for caching purpose).

### Method `appendPath(string $path[, string $group])`

Add a configuration directory path add the end of the group.
The Default value for `$group` is "`__main__`"

### Method `prependPath(string $path[, string $group])`

Add a configuration directory path add the beginning of the group.
The Default value for `$group` is "`__main__`"

### Method `orderGroupPath(string $group[, string $before[, string $after])`

Define where the group must be positioned relatively to other group.
The default value for `$before` is `null`.
The default value for `$after` is `null`.

For more details read documentation [OrderedGroup->setGroupPosition()](OrderedGroup.md#Method-setGroupPositionstring-groupName-string-before-string-after)

### Method `appendReader(ReaderInterface $reader)`

Add a reader at the end of the list of readers to use.

### Method `prependReader(ReaderInterface $reader)`

Add a reader at the beginning of the list of readers to use.

### Method `appendKeyCallback(callable $callback)`

Add a key callback at the end at the key callback list.

For more information about key callback, read the chapter about callback of the document.

### Method `prependKeyCallback(callable $callback)`

Add a key callback at the beginning at the key callback list.

For more information about key callback, read the chapter about callback of the document.

### Method `appendValueCallback(callable $callback)`

Add a value callback at the end at the value callback list.

For more information about value callback, read the chapter about callback of the document.

### Method `prependValueCallback(callable $callback)`

Add a value callback at the beginning at the value callback list.

For more information about value callback, read the chapter about callback of the document.


Callback function (for key and value)
-------------------------------------

### Key callback

You can define one (or more) callback that can modify a key before been read by the Config object.

The function have one parameter: the initial key.
The function **must** return a string that will be the new key.

### Value callback

You can define one (or more) callback that can modify a key after it's read from config and before it's send back to your application.

The function have two parameters:
 1. the initial key
 1. The read value

The function **must** return a value that will be the new value.


Implementations
---------------

The class implement two interfaces:
 - `\IteratorAggregate`, for `foreach` use. (read-only)
 - `\Countable`, to get the number of configurations