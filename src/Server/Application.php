<?php

namespace Websyspro\HttpRequest\Server;

class Application
{
  public Request $request;
  public Response $response;

  public static string $defaultApiBase = "api/v1";
  public static string $defaultApiPort = "80";

  public static array $database = [
    "host" => "localhost",
    "user" => "root",
    "pass" => "12345678",
    "name" => "dataapi",
    "port" => "3307"
  ];

  public function __construct(
    public string $apiBase =  "api/v1",
    public string $apiPort = "8080",
    public array $controllers = [],
    public array $entitys = []
  ) {
    $this->createApp();
    $this->createEntitys();
    $this->createControllers();
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