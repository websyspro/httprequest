<?php

namespace Websyspro\HttpRequest\Decorations\Columns;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BigInt {
  public function __construct(){}
}