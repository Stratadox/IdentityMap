# Identity Map

[![Build Status](https://travis-ci.org/Stratadox/IdentityMap.svg?branch=master)](https://travis-ci.org/Stratadox/IdentityMap)
[![Coverage Status](https://coveralls.io/repos/github/Stratadox/IdentityMap/badge.svg?branch=master)](https://coveralls.io/github/Stratadox/IdentityMap?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stratadox/IdentityMap/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stratadox/IdentityMap/?branch=master)
[![Infection Minimum](https://img.shields.io/badge/msi-100-brightgreen.svg)](https://travis-ci.org/Stratadox/IdentityMap)
[![PhpStan Level](https://img.shields.io/badge/phpstan-7/7-brightgreen.svg)](https://travis-ci.org/Stratadox/IdentityMap)
[![Maintainability](https://api.codeclimate.com/v1/badges/8c27d62a028e929648d2/maintainability)](https://codeclimate.com/github/Stratadox/IdentityMap/maintainability)
[![Latest Stable Version](https://poser.pugx.org/stratadox/identity-map/v/stable)](https://packagist.org/packages/stratadox/identity-map)
[![License](https://poser.pugx.org/stratadox/identity-map/license)](https://packagist.org/packages/stratadox/identity-map)

Maps objects by identity.

## About

Contains the objects that have already been loaded.

Mainly used to prevent double loading of unique entities, and as registry of the
loaded entities.

## Installation

Install with `composer require stratadox/identity-map`

## What is this?

An [Identity Map](https://www.martinfowler.com/eaaCatalog/identityMap.html) is a 
registry of all the entities that have been loaded from the data source.

Client code can consult the identity map before performing expensive data 
retrieval operations (such as querying a database or requesting online resources)

## How does it work?

It's essentially just an immutable map of maps with objects.

The first layer maps classes to the map of loaded objects for that class.
The second layer maps from the identity to the actual object.

Additionally, it contains a reverse map to quickly map an instance to its id.

## How to use this?

### Making a map

Either create a map pre-filled with objects:
```php
$map = IdentityMap::with([
    'id1' => $object1,
    'id2' => $object2,
]);
```
Or start with a blank map:
```php
$map = IdentityMap::startEmpty();
```
...and later fill it up with objects:
```php
$map = $map->add('id3', $object3);
```
Objects can be removed from the map by using:
```php
$map = $map->remove(Foo::class, 'id3');
```
Or:
```php
$map = $map->removeThe($object);
```
Entire classes can be removed with:
```php
$map = $map->removeAllObjectsOfThe(Foo::class);
```

### Consulting the map

To check whether the entity with the requested id already exists in the map:
```php
if ($map->has(Foo::class, '1')) { ...
```
To retrieve the corresponding object from the map:
```php
$object = $map->get(Foo::class, '1');
```
To check whether the object instance was added:
```php
if ($map->hasThe($object)) { ...
```
To retrieve the id of an object that is in the map:
```php
$id = $map->idOf($object);
```

### Preventing unwanted classes

When loading a bunch of objects that may consist of both entities and value 
objects, one may want to ignore the value objects when provisioning the identity
map.

This can be done by wrapping the identity map:
```php
$map = Whitelist::forThe(IdentityMap::startEmpty(), MyEntity::class);
```
Adding objects that are not, in this case, of the `MyEntity` class, will be 
silently ignored.

Multiple entities can be whitelisted by specifying more classes:
```php
$map = Whitelist::forThe(IdentityMap::startEmpty(), Foo::class, Bar::class);
```
Or using this shortcut:
```php
$map = Whitelist::the(Foo::class, Bar::class);
```
