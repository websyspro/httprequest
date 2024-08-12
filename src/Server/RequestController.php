<?php

namespace Websyspro\Core\Server;

use ReflectionAttribute;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ConstructStructure;
use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\Method;
use Websyspro\Core\Enums\MiddlewareStructure;

class RequestController
{
  public RequestControllerItem $requestControllerItem;

  public function __construct(
    private string $controller
  ){
    $this->requestControllerItem = RequestControllerItem::create(
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
      "%s{$controller}", DIRECTORY_SEPARATOR_WINDOWS
    );
  }

  private function getControllerUrl(
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
  
  private function getControllerMiddlewares(
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

  /**
   * @Create
   * 
   * Create controller instance
   * @param string $controller
   * **/
  public static function create(
    string $controller
  ): RequestController {
    return new static(
      $controller
    );
  }
}