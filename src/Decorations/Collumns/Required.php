<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required
{
  public function __construct(){}

  public function get(): array {
    return [
      "required" => "Sim"
    ];
  }
}