<?php

namespace Websyspro\HttpRequest\Models\Decorations\Columns;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Varchar {
  public function __construct(
    private int $size = 255
  ){}
}