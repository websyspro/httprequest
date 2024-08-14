<?php

namespace Websyspro\HttpRequest\Common;

class Utils
{
  public static function ArrayFirtsValue(
    mixed $iterable
  ): string | Array | null {
    return array_shift(
      $iterable
    );
  }

  public static function Map(
    mixed $iterable,
    callable $callable
  ): array {
    return array_map(
      $callable, (array)$iterable
    );
  }

  public static function MapKey(
    iterable $iterable,
    callable $callable,
        array $iterableArr = []
  ): array {
    foreach($iterable as $key => $val){
      $iterableArr[$key] = $callable($val, $key);
    }

    return $iterableArr;
  }

  public static function Filter(
    mixed $iterable,
    callable $callable
  ): array {
    return array_values( array_filter(
      $iterable, $callable
    ));
  }

  public static function FilterKey(
    iterable $iterable,
    callable $callable,
        array $iterableArr = []
  ): array {
    foreach($iterable as $key => $val){
      if ($callable($val, $key)) {
        $iterableArr[$key] = $val;
      }
    }

    return $iterableArr;
  }
}