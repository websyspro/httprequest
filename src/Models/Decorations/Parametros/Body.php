<?php

namespace Websyspro\HttpRequest\Models\Decorations\Parametros;

use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Server\Request;
use Websyspro\HttpRequest\Server\Response;
use Attribute;

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
    if(isset($request->fieldDataList->dataList)){
      return $request->fieldDataList->dataList;
    } else return [];
  }  
}