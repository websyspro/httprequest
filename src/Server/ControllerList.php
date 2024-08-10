<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Common\Utils;

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
          controllerRequest: $this->request,
          controllerResponse: $this->response,
          controller: ServerUtils::getControllerName($controller),
          controllerUrl: ServerUtils::GetControllerApi($controller),
          controllerConstruct: ServerUtils::GetConstruct($controller),
          controllerMiddlewares: ServerUtils::GetMiddlewares($controller)
        )
      )
    ));
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