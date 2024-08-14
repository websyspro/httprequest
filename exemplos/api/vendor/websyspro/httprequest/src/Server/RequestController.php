<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionAttribute;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Enums\ConstructStructure;
use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Enums\Method;
use Websyspro\HttpRequest\Enums\MiddlewareStructure;

class RequestController {
  public RequestControllerItem $requestControllerItem;

  public function __construct(
    private string $controller,
    private Request $request
  ){
    $this->requestControllerItem = RequestControllerItem::create(
      request: $this->request,
      controller: $this->getController($this->controller),
      controllerUrl: $this->getControllerUrl($this->controller),
      controllerName: $this->getControllerName($this->controller),
      controllerConstruct: $this->getControllerConstruct($this->controller),
      controllerMiddlewares: $this->getControllerMiddlewares($this->controller)
    );
  }

  private function getController(
    string $controller
  ): string {
    return sprintf(
      "%s{$controller}", "\\"
    );
  }

  private function getControllerUrl(
    string $controller
  ): string {
    return implode( "/", [
      $this->request->application->apiBase,
      $this->GetController(
        $controller
      )
    ]);    
  }

  private function getControllerName(
    string $controller
  ): string {
    return preg_replace(
      "/controller$/", "", mb_strtolower(
        $controller
      )
    );
  }
  
  private function getControllerConstruct(
    string $controller
  ): array {
    $constrctFromController = Utils::Filter(get_class_methods($controller),
      fn($method) => $method === Method::Contruct
    );

    $parametersFromContruct = Utils::Map($constrctFromController,
      fn($method) => Reflect::getReflectMethod(
        $controller, $method
      )->getParameters()
    );

    $parametersFromContruct = Utils::Map($parametersFromContruct,
      fn($parameters) => Utils::Map($parameters, 
        fn($parameter) => "\\" . $parameter->getType()->getName()
      )
    );

    return [
      ConstructStructure::MethodName => Method::Contruct,
      ConstructStructure::MethodParameters => Utils::ArrayFirtsValue(
        $parametersFromContruct
      )
    ];
  } 
  
  private function getControllerMiddlewares(
    string $controller
  ): array {
    $MiddlewareFromController = Utils::Filter(Reflect::getAttributesFromReflectClass($controller),
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Middleware
      )
    );

    return Utils::Map($MiddlewareFromController, fn($attribute) => [
      MiddlewareStructure::InstanceClass => "\\" . $attribute->getName(),
      MiddlewareStructure::ArgsClass => $attribute->getArguments()
    ]);
  }   

  public static function create(
    string $controller,
    Request $request
  ): RequestController {
    return new static(
      controller: $controller,
      request: $request
    );
  }
}