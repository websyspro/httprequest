<?php

namespace Websyspro\Core\Models\Decorations;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
  public function __construct(
    private string $controllerName
  ){}
}