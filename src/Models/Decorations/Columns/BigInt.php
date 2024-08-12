<?php

namespace Websyspro\HttpRequest\Models\Decorations\Columns;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BigInt {
  public function __construct(){}
}