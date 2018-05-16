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
Used to prevent double loading of supposedly unique entities.

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

## How to use this?

### Making a map

Either create a map pre-filled with objects:
```php
$map = IdentityMap::with([
    'id1' => $object1,
    'id2' => $object2,
])
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

### Consulting the map

To check whether the entity with the requested id already exists in the map:
```php
if ($map->has(Foo::class, '1')) { ...
```
To retrieve the corresponding object from the map:
```php
$object = $map->get(Foo::class, '1');
```
To retrieve the id of an object that is in the map:
```php
$id = $map->idOf($object);
```
