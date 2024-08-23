<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionAttribute;
use ReflectionParameter;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Enums\Method;
use Websyspro\HttpRequest\Enums\MiddlewareStructure;

class RequestControllerItem
{
  public RouterList $routerList;

  public function __construct(
    public Request $request,
    public string $controller,
    public string $controllerUrl,
    public string $controllerName,
    public array $controllerConstruct,
    public array $controllerMiddlewares,
  ){
    $this->routeList();
  }

  private function routeList(
  ): void {
    if($this->routerList = RouterList::create()){
      $methodsInController = Utils::Filter(
        get_class_methods($this->controller), fn(string $method) => (
          $method !== Method::Contruct
        )
      );

      if (sizeof($methodsInController)){
        Utils::Map($methodsInController, fn($method) => (
          $this->routerList->addRouter(
            RequestControllerRouterItem::create(
              route: $this->getRoute($method),
              routeUri: $this->getRouteUrl($method),
              routeName: $this->getRouteName($method),
              routeMethodType: $this->getRouteType($method),
              routeParameters: $this->getRouteParameters($method),
              routeParametersArgs: $this->getRouteParametersArgs($method),
              routeMiddleware: $this->getRouteMiddleware($method),
            )
          )
        ));
      };
    }
  }

  private function getAttributesFromMethods(string $method): array
  {
    return Reflect::getAttributesFromReflectMethod(
      $this->controller, $method
    );
  }

  private function getRoute(
    string $method
  ): string {
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
    return implode( "/", [
      $this->getApiBase(),
      $this->getController(),
      $this->getRoute(
        $method
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
          fn($attribute) => "\\" . $attribute->getName()
        ))
    );

    return $AttributesFromParametersFromMethod;
  } 

  private function getRouteParametersArgs(
    string $Method
  ): array {
    $methodFromClass = Reflect::getReflectClass(
      $this->controller
    )->getMethod($Method);

    $test = Utils::Map($methodFromClass->getParameters(), fn(ReflectionParameter $parmeter) => (
      Utils::Map($parmeter->getAttributes(), 
        fn($attributes) => $attributes->getArguments()
      )[0]
    ));

    return $test;
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
  
  public function getController(
  ): string {
    $AttributeFromController = Utils::Filter(Reflect::getAttributesFromReflectClass($this->controller), 
      fn(ReflectionAttribute $controller) => (
        $controller->getName()::TypeDecoration === Decoration::Controller
      )
    );

    $ApiController = Utils::Map($AttributeFromController, 
      fn($api) => Utils::ArrayFirtsValue($api->getArguments())
    );

    return Utils::ArrayFirtsValue(
      $ApiController
    );
  } 
  
  public function getApiBase(
  ): string {
    return "/{$this->request->application->apiBase}";
  }

  public static function create(
    Request $request,
    string $controller,
    string $controllerUrl,
    string $controllerName,
     array $controllerConstruct,
     array $controllerMiddlewares
  ): RequestControllerItem {
    return new static(
      request: $request,
      controller: $controller,
      controllerUrl: $controllerUrl,
      controllerName: $controllerName,
      controllerConstruct: $controllerConstruct,
      controllerMiddlewares: $controllerMiddlewares
    );
  } 
}