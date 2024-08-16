<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionClass;
use ReflectionMethod;

class Reflect
{
  public static function getReflectClass(
    string $reflectClass
  ): ReflectionClass {
    return new ReflectionClass(
      $reflectClass
    );
  }

  public static function getAttributesFromReflectClass(
    string $Controller
  ): array {
    return static::getReflectClass(
      $Controller
    )->getAttributes();
  }

  public static function getPropertiesFromReflectClass(
    string $Controller
  ): array {
    return static::getReflectClass(
      $Controller
    )->getProperties();
  }  

  public static function getReflectMethod(
    string $reflectClass,
    string $reflectMethod
  ): ReflectionMethod {
    return new ReflectionMethod(
      $reflectClass,
      $reflectMethod
    );
  }

  public static function getParametersFromReflectMethod(
    string $reflectClass,
    string $reflectMethod
  ): array {
    return static::getReflectMethod(
      $reflectClass,
      $reflectMethod
    )->getParameters();
  }
  
  public static function getAttributesFromReflectMethod(
    string $reflectClass,
    string $reflectMethod
  ): array {
    return static::getReflectMethod(
      $reflectClass,
      $reflectMethod
    )->getAttributes();
  }  
}