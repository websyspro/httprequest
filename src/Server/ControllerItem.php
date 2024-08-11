<?php

namespace Websyspro\Core\Server;

use ReflectionAttribute;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\Method;
use Websyspro\Core\Enums\MiddlewareStructure;

class ControllerItem
{
  public RouterList $routerList;

  public function __construct(
    public Request $controllerRequest,
    public Response $controllerResponse,
    public string $controller,
    public string $controllerUrl,
    public array $controllerConstruct,
    public array $controllerMiddlewares,
  ){
    $this->routeList();
  }

  private function routeList(): void
  {
    if($this->routerList = RouterList::create())
    {
      $methodsInController = Utils::Filter(
        get_class_methods($this->controller), fn(string $method) => (
          $method !== Method::Contruct
        )
      );

      if (sizeof($methodsInController))
      {
        Utils::Map($methodsInController, fn($method) => (
          $this->routerList->addRouter(
            RouterItem::create(
              request: $this->controllerRequest,
              response: $this->controllerResponse,
              route: $this->getRoute($method),
              routeUri: $this->getRouteUrl($method),
              routeName: $this->getRouteName($method),
              routeMethodType: $this->getRouteType($method),
              routeParameters: $this->getRouteParameters($method),
              routeMiddleware: $this->getRouteMiddleware($method),
            )
          )
        ));
      };
    }
  }

  /**
   * @RouteInit
   * 
   * Locate the router for the requested URL Request
   * @param: none
   * **/
  public function routeInit(
  ): void {
    Utils::Filter($this->routerList->routers, 
      fn(RouterItem $routerItem) => var_dump($routerItem->routeUri)
    );
  }

  private function getAttributesFromMethods(string $method): array
  {
    return Reflect::getAttributesFromReflectMethod(
      $this->controller, $method
    );
  }

  private function getRoute(string $method): string 
  {
    $routeFromMethods = Utils::Filter(
      $this->getAttributesFromMethods($method), fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Route
      )
    );

    $ApiController = Utils::Map($routeFromMethods, 
      fn($api) => Utils::ArrayFirtsValue(
        $api->getArguments()
      )
    );

    return Utils::ArrayFirtsValue(
      empty( $ApiController[0]) === false 
        ? $ApiController : [ "" ]
    );
  }

  private function getRouteUrl(
    string $method    
  ): string {
    return implode(
      ServerUtils::GetApiBarSep(), [
      ServerUtils::GetApiBase(), 
      ServerUtils::GetController($this->controller),
      ServerUtils::GetRoute(
        $this->controller, $method
      )
    ]);
  }

  private function getRouteName(
    string $method
  ): string {
    return $this->controller ? $method : $method;
  } 
  
  private function getRouteType(
    string $method
  ): string {
    $AttributesFromMethod = Reflect::getReflectMethod(
      $this->controller, $method
    )->getAttributes();

    $AttributesFromMethod = Utils::Filter($AttributesFromMethod, 
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Route
      )
    );

    $AttributesFromMethod = Utils::Map(
      $AttributesFromMethod,
        fn($Attribute) => (string)$Attribute->getName()::TypeHttp
    );

    return Utils::ArrayFirtsValue(
      $AttributesFromMethod
    );
  }
  
  private function getRouteParameters(
    string $Method
  ): array {
    $ParametersFromMethod = Reflect::getParametersFromReflectMethod(
      $this->controller, $Method
    );

    $AttributesFromParametersFromMethod = Utils::Map($ParametersFromMethod, 
      fn($parameters) => $parameters->getAttributes()
    );

    $AttributesFromParametersFromMethod = Utils::Map(
      $AttributesFromParametersFromMethod, 
        fn($attributes) => Utils::ArrayFirtsValue(Utils::Map($attributes,
          fn($attribute) => DIRECTORY_SEPARATOR_WINDOWS . $attribute->getName()
        ))
    );

    return $AttributesFromParametersFromMethod;
  } 
  
  private function getRouteMiddleware(
    string $method
  ): array {
    $AttributesFromMethod = Reflect::getAttributesFromReflectMethod(
      $this->controller, $method
    );

    $MiddlewareFromMethod = Utils::Filter($AttributesFromMethod,
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Middleware
      )
    );

    return Utils::Map($MiddlewareFromMethod,
      fn($attribute) => [
        MiddlewareStructure::InstanceClass => $attribute->getName(),
        MiddlewareStructure::ArgsClass => $attribute->getArguments()
      ]
    );
  }  

  public static function create(
    Request $controllerRequest,
    Response $controllerResponse,
    string $controller,
    string $controllerUrl,
     array $controllerConstruct,
     array $controllerMiddlewares
  ): ControllerItem {
    return new static(
      controller: $controller,
      controllerUrl: $controllerUrl,
      controllerRequest: $controllerRequest,
      controllerResponse: $controllerResponse,
      controllerConstruct: $controllerConstruct,
      controllerMiddlewares: $controllerMiddlewares
    );
  } 
}