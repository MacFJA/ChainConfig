Class OrderedGroup
==================

Features
--------

* You can group value
* Order in group matters
* You can order group

---

The class is covered at 100% with PHPUnit.

---

The class implements the following interfaces:
 - `\IteratorAggregate` for `foreach` use
 - `\Countable` to get the number of values (usable with PHP `count()` function)
 - `\Serializable`

API
---

### Method `appendValue(string $value[, string $group])`

Add a value at the end of the group.
By default the group is `__main__`.

### Method `prependValue(string $value[, string $group])`

Add a value at the beginning of the group.
By default the group is `__main__`.

### Method `setGroupPosition(string $groupName[, string $before[, string $after]])`

Set the relative (to others group) position of a group.
You can define after which group (`$after` parameter) your group must be.
You can define before which group (`$before` parameter) your group must be.

### Method `getGroupValues([string $group])`

Get all values of a group.
By default the group is `__main__`.

### Method `getAllGroups()`

Get the list of all group.
The result is not ordered (see `getSortedGroups` for ordered list).

### Method `getSortedGroups()`

Get the ordered (according to the relative position define with `setGroupPosition`) list of group.

### Method `getAllValues()`

Get all ordered (group order and in group order) values.