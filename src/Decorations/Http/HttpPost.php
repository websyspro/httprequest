<?php

namespace Websyspro\HttpRequest\Decorations\Http;

use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Enums\HttpType;
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