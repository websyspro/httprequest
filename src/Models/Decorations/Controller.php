<?php

namespace Websyspro\Core\Models\Decorations;

use Websyspro\Core\Enums\Decoration;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
  public const TypeDecoration = Decoration::Controller;

  public function __construct(
    public string $controllerName
  ){}
}