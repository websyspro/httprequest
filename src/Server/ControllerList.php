<?php

namespace Websyspro\Core\Server;

use ReflectionAttribute;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ConstructStructure;
use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\Method;
use Websyspro\Core\Enums\MiddlewareStructure;

class ControllerList
{
  private array $controllers = [];

  public function __construct(
    private Request $request,
    private Response $response,
    private array $moduleControllers
  ){
    $this->controllerList();
  }

  public function controllerList(): void {
    Utils::Map($this->moduleControllers, fn(string $controller) => (
      $this->addController(
        ControllerItem::create(
          controllerRequest: $this->getControllerRquest(),
          controllerResponse: $this->getControllerResponse(),
          controller: $this->getController($controller),
          controllerUrl: $this->getControllerUrl($controller),
          controllerConstruct: $this->getControllerConstruct($controller),
          controllerMiddlewares: $this->getControllerMiddlewares($controller)
        )
      )
    ));
  }

  public function getControllerRquest(): Request {
    return $this->request;
  }

  public function getControllerResponse(): Response {
    return $this->response;
  }

  public function getController(
    string $controller
  ): string {
    return sprintf(
      "%s{$controller}", DIRECTORY_SEPARATOR_WINDOWS
    );
  }

  public function getControllerUrl(
    string $controller
  ): string {
    return implode(
      ServerUtils::GetApiBarSep(), [
      ServerUtils::GetApiBase(),
      ServerUtils::GetController(
        $controller
      )
    ]);    
  }
  
  public function getControllerConstruct(
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
        fn($parameter) => DIRECTORY_SEPARATOR_WINDOWS . $parameter->getType()->getName()
      )
    );

    return [
      ConstructStructure::MethodName => Method::Contruct,
      ConstructStructure::MethodParameters => Utils::ArrayFirtsValue(
        $parametersFromContruct
      )
    ];
  } 
  
  public function getControllerMiddlewares(
    string $controller
  ): array {
    $MiddlewareFromController = Utils::Filter(Reflect::getAttributesFromReflectClass($controller),
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Middleware
      )
    );

    return Utils::Map($MiddlewareFromController, fn($attribute) => [
      MiddlewareStructure::InstanceClass => DIRECTORY_SEPARATOR_WINDOWS . $attribute->getName(),
      MiddlewareStructure::ArgsClass => $attribute->getArguments()
    ]);
  }  

  public function addController(
    mixed $controller
  ): void {
    $this->controllers[] = $controller;
  }

  public static function create(
    Request $request,
    Response $response,
    array $moduleControllers
  ): ControllerList {
    return new static(
      request: $request,
      response: $response,
      moduleControllers: $moduleControllers
    );
  }
}