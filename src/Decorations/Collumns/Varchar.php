<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Varchar 
{
  public function __construct(
    private int $size = 0
  ){}

  public function get(): array {
    return [
      "type" => "Varchar({$this->size})"
    ];
  }
}