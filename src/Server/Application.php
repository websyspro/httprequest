<?php

namespace Websyspro\HttpRequest\Server;

class Application
{
  public Request $request;
  public Response $response;

  public static $defaultApiBase = "api/v1";
  public static $defaultApiPort = "80";

  public function __construct(
    public string $apiBase =  "api/v1",
    public string $apiPort = "8080",
    public array $controllers = [],
    public array $entitys = []
  ) {
    $this->createApp();
    $this->createControllers();
  }

  public function createApp(
  ): void {
    $this->request = Request::create($this);
    $this->response = Response::create($this);
  }

  public function createControllers(
  ): void {
    $this->request->setControllers(
      $this->controllers
    );
  }

  public static function create(
    string $apiBase =  "api/v1",
    string $apiPort = "8080",
     array $controllers = [],
     array $entitys = []
  ): Application {
    return new static(
      apiBase: $apiBase,
      apiPort: $apiPort,
      controllers: $controllers,
      entitys: $entitys
    );
  }  
}