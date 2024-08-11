<?php

namespace Websyspro\Core\Server;

use ReflectionAttribute;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ConstructStructure;
use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\HttpStatus;
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
    $this->controllerRouterItems();
    $this->controllerInit();
  }

  private function controllerRouterItems(): void {
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

  private function getRequestPathArr(): array {
    return explode(
      DIRECTORY_SEPARATOR_LINUX, preg_replace(
        "/\/$/", "", $this->request->requestUri
      ) 
    );
  }

  private function isControllerExists(
    array $controllerArr
  ): bool {
    return is_array(
      $controllerArr
    ) && sizeof( $controllerArr );
  }

  /**
   * @ControllerInit
   * 
   * Locate the controller for the requested URL Request
   * @param: none
   * **/
  private function controllerInit(
  ): void {
    /**
     * Search the list of controls
     * **/
    if(sizeof($this->getRequestPathArr()) >= 4) {
      $controllerArr = Utils::Filter($this->controllers, 
        fn(ControllerItem $controller) => (
          $this->getRequestControllerBaseUrl() === $controller->controllerUrl
        )
      );

      /**
       * Check if controller exists
       * **/
      if($this->isControllerExists($controllerArr)){
        Utils::Map($controllerArr, 
          fn(ControllerItem $controllerItem) => (
            $controllerItem->routeInit()
          )
        );
      } else {

        /**
         * Mesasge Error Controller NotFound
         * **/
        $this->response->Error(
          "Cannot {$this->request->RequestMethod} {$this->request->requestUri}", HttpStatus::NotFound
        );
      }
    } else {

      /**
       * Service initialized success message
       * **/
      $this->response->Send(
        "Service initialized success message"
      );
    }
  }

  private function getRequestControllerBaseUrl(): string {
    [ , $apiBase, $apiVer, $apiController ] = $this->getRequestPathArr();
    return DIRECTORY_SEPARATOR_LINUX . implode( DIRECTORY_SEPARATOR_LINUX, [
      $apiBase, $apiVer, $apiController 
    ]);
  }

  private function getControllerRquest(): Request {
    return $this->request;
  }

  private function getControllerResponse(): Response {
    return $this->response;
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

  private function addController(
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