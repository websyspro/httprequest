<?php

namespace Websyspro\Core\Server;

class RouterItem
{
  public function __construct(
    public Request $request,
    public Response $response,
    public string $route,
    public string $routeUri,
    public string $routeName,
    public string $routeMethodType,
    public  array $routeParameters,
    public  array $routeMiddleware
  ){}

  public static function create(
    Request $request,
    Response $response,    
    string $route,
    string $routeUri,
    string $routeName,
    string $routeMethodType,
     array $routeParameters,
     array $routeMiddleware
  ): RouterItem {
    return new static(
      request: $request,
      response: $response,
      route: $route,
      routeUri: $routeUri,
      routeName: $routeName,
      routeMethodType: $routeMethodType,
      routeParameters: $routeParameters,
      routeMiddleware: $routeMiddleware
    );
  } 
}