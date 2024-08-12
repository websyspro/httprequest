<?php

namespace Websyspro\HttpRequest\Models\Decorations\Http;

use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Enums\HttpType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class HttpGet
{
  public const TypeDecoration = Decoration::Route;
  public const TypeHttp = HttpType::GET;

  public function __construct(
    public string $route = ""
  ){}
}