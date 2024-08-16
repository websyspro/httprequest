<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TinyInt
{
  public function __construct(){}

  public function get(): array {
    return [
      "type" => "TinyInt"
    ];
  }
}