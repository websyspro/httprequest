<?php

namespace Websyspro\HttpRequest\Server;

class Application
{
  public Request $request;
  public Response $response;

  public static string $defaultApiBase = "api/v1";
  public static string $defaultApiPort = "80";

  public static array $config = [];

  public function __construct(
    public string $apiBase =  "api/v1",
    public string $apiPort = "8080",
    public array $database = [],
    public array $controllers = [],
    public array $entitys = []
  ) {
    $this->createConfig();
    $this->createApp();
    $this->createEntitys();
    $this->createControllers();
  }

  public function createConfig(
  ): void {
    Application::$config = [
      "database" => $this->database
    ];
  }

  public function hasControllersList(
  ): bool {
    return is_array($this->controllers) 
        && sizeof($this->controllers) !== 0; 
  }

  public function hasEntitysList(
  ): bool {
    return is_array($this->entitys) 
        && sizeof($this->entitys) !== 0; 
  }  

  public function createApp(
  ): void {
    if($this->hasControllersList()){
      $this->request = Request::create($this);
      $this->response = Response::create($this);  
    }
  }

  public function createEntitys(
  ): void {
    if ($this->hasEntitysList()) {
      Migrations::create(
        entitys: $this->entitys
      );
    }
  }

  public function createControllers(
  ): void {
    if ($this->hasControllersList()){
      $this->request->setControllers(
        $this->controllers
      );
    }
  }

  public static function create(
    string $apiBase =  "api/v1",
    string $apiPort = "8080",
     array $database = [],
     array $controllers = [],
     array $entitys = []
  ): Application {
    return new static(
      apiBase: $apiBase,
      apiPort: $apiPort,
      database: $database,
      controllers: $controllers,
      entitys: $entitys
    );
  }  
}