<?php

namespace Websyspro\HttpRequest\Server;

class Application
{
  public Request $request;
  public Response $response;

  public function __construct(
    public string $apiBase =  "api/v1",
    public string $apiPort = "8080",
    public array $controllers = [],
    public array $modules = []
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
     array $modules = []
  ): Application {
    return new static(
      apiBase: $apiBase,
      apiPort: $apiPort,
      controllers: $controllers,
      modules: $modules
    );
  }  
}