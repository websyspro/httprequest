<?php

namespace Websyspro\HttpRequest\Common;

class Utils
{
  public static function Join(
    array $arr = [],
    string $eparetor = ","
  ): string {
    return implode( $eparetor, $arr );
  }

  public static function Split(
    string $texto,
    string $separetor
  ): array {
    return explode(
      $separetor,
      $texto
    );
  }

  public static function ArrayFirtsValue(
    mixed $iterable
  ): string | Array | null {
    return array_shift(
      $iterable
    );
  }

  public static function ArrayLastValue(
    mixed $iterable
  ): string | Array | null {
    $reverseIterable = array_reverse(
      $iterable
    );

    return array_shift(
      $reverseIterable
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

  public static function Date(): string {
    return date("Y-m-d H:i:s");
  }

  public static function Str(
    string $strTexto
  ): string {
    return $strTexto;
  }
}