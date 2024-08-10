<?php

namespace Websyspro\Core\Models\Decorations\Parametros;

use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Server\Request;
use Attribute;
use Websyspro\Core\Server\Response;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body
{
  public const TypeDecoration = Decoration::RouteParameters;

  public function __construct(
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): array {
    return [];
  }  
}