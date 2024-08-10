<?php

namespace Websyspro\Core\Models\Decorations\Http;

use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\HttpType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class HttpPost
{
  public const TypeDecoration = Decoration::Route;
  public const TypeHttp = HttpType::POST;

  public function __construct(
    public string $route
  ){}
}