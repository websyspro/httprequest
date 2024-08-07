<?php

namespace Websyspro\Core\Models\Decorations;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Model {
  public function __construct() {}
}