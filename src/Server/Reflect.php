<?php

namespace Websyspro\Core\Server;

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

  public static function getReflectMethod(
    string $reflectClass,
    string $reflectMethod
  ): ReflectionMethod {
    return new ReflectionMethod(
      $reflectClass,
      $reflectMethod
    );
  }
}