<?php

namespace Websyspro\HttpRequest\Decorations\Parametros;

use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Server\Request;
use Websyspro\HttpRequest\Server\Response;
use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Param
{
  public const TypeDecoration = Decoration::RouteParameters;

  public function __construct(
    private string $paramKey = ""
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): array | string {
    if(isset($request->requestParams)){
      if(empty($this->paramKey)){
        return $request->requestParams;  
      } else return $request->requestParams[$this->paramKey];
    } else return [];
  }  
}