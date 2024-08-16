<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Decimal
{
  public function __construct(){}

  public function get(): array {
    return [
      "type" => "Decimal(10,4)"
    ];
  }
}