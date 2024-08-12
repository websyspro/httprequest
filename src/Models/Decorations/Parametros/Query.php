<?php

namespace Websyspro\Core\Models\Decorations\Parametros;

use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Server\Request;
use Websyspro\Core\Server\Response;
use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Query
{
  public const TypeDecoration = Decoration::RouteParameters;

  public function __construct(
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): array {
    if(isset($request->requestQuerys)){
      return $request->requestQuerys;
    } else return [];
  }  
}