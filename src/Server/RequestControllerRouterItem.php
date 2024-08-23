<?php

namespace Websyspro\HttpRequest\Server;

class RequestControllerRouterItem
{
  public function __construct(
    public string $route,
    public string $routeUri,
    public string $routeName,
    public string $routeMethodType,
    public array $routeParameters,
    public array $routeParametersArgs,
    public array $routeMiddleware
  ){}

  public static function create(
    string $route,
    string $routeUri,
    string $routeName,
    string $routeMethodType,
     array $routeParameters,
     array $routeParametersArgs,
     array $routeMiddleware
  ): RequestControllerRouterItem {
    return new static(
      route: $route,
      routeUri: $routeUri,
      routeName: $routeName,
      routeMethodType: $routeMethodType,
      routeParameters: $routeParameters,
      routeParametersArgs: $routeParametersArgs,
      routeMiddleware: $routeMiddleware
    );
  } 
}