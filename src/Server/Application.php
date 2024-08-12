<?php

namespace Websyspro\Core\Server;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ControllerStructure;
use Websyspro\Core\Enums\HttpStatus;
use Websyspro\Core\Enums\HttpType;
use Websyspro\Core\Enums\MethodStructure;
use Websyspro\Core\Enums\Module;
use Websyspro\Core\Enums\ServerType;

class Application
{
  private Request $Request;
  private Response $Response;
  private ControllerList $controllerList;
  private array $Controllers;
  private array $Models;

  public function __construct(
    private array $Modules = []
  ) {
    $this->createApp();
    $this->createControllers();

    // $this->CreateControllersFilter();
    // $this->CreateControllersRoutersFilter();
    // $this->CreateControllersExecute();
  }

  public function createApp(): void {
    $this->Request = Request::create();
    $this->Response = Response::create();

    print_r($this->Request);
  }

  public function getModules(): array {
    return $this->Modules[Module::Controllers];
  }

  public function createControllers(): void
  {
    // $this->controllerList = ControllerList::create(
    //   $this->Request, $this->Response, $this->getModules()
    // );
  }

  public function CreateControllersFilter(int $pathArr = 0): void
  {
    $pathArr = explode(
      DIRECTORY_SEPARATOR_LINUX, $this->Request->getRequestUri()
    );

    if(sizeof($pathArr) >= 4)
    {
      [, $apiBase, $apiVersion, $apiController] = $pathArr;
  
      $this->Controllers = Utils::Filter($this->Controllers, 
        function($Controller) use( $apiBase, $apiVersion, $apiController ) {
          return $Controller[
            ControllerStructure::RequestApi
          ] === implode( "", [
            DIRECTORY_SEPARATOR_LINUX, $apiBase,
            DIRECTORY_SEPARATOR_LINUX, $apiVersion,
            DIRECTORY_SEPARATOR_LINUX, $apiController
          ]);
        }
      );    
    }
  }

  public function FilterValidMethodType(
    array $Route
  ): bool {
    return $Route[MethodStructure::MethodType]
       === $this->Request->getRequestMethod();
  }

  public function FilterValidRoute(
    array $Route,
    array $RouteController = [],
    array $RouteRequest = [],
    array $RouteControllerValid = [],
   string $RegExpValidedRouter = '/\{\w*\:\w*|\w*\}/'
  ) : bool {
   if (preg_match($RegExpValidedRouter, $Route[MethodStructure::MethodUri])) {
     $RouteController = explode(DIRECTORY_SEPARATOR_LINUX, $Route[MethodStructure::MethodUri]);
     $RouteRequest = explode(DIRECTORY_SEPARATOR_LINUX, $this->Request->uriSufixo());

     if (sizeof($RouteController) !== sizeof($RouteRequest)) {
       return false;
     }

     $RouteControllerValid = Utils::Map($RouteController, function($Route)
       use (
         $RegExpValidedRouter,
         $RouteController,
         $RouteRequest
       ){
         if (preg_match($RegExpValidedRouter, $Route)) {
           return true;
         } else
         if ($RouteRequest[array_search($Route, $RouteController)] === $Route) {
           return true;
         } else return false;
       });

     if (sizeof(array_diff($RouteControllerValid, [true])) === 0){
       Utils::MapKey($RouteController, function($Param, $Key) use(
         $RegExpValidedRouter,
         $RouteRequest
       ) {
         if (preg_match($RegExpValidedRouter, $Param)) {
           [$ParamString, $ParamType] = explode(
             ":", preg_replace('/\{|\}/', '', $Param)
           );

          switch($ParamType){
            // case "int":
            //   $this->Request->addParams(
            //     $ParamString, (int)$RouteRequest[$Key]
            //   );
            //   break;
            // case "string":
            //   $this->Request->addParams(
            //     $ParamString, (string)$RouteRequest[$Key]
            //   );
            //   break;
            // default:
            //   $this->Request->addParams(
            //     $ParamString, (string)$RouteRequest[$Key]
            //   );
           }
         }
       });
       return true;
     } else return false;
   } else {
     return $Route[MethodStructure::MethodUri]
        === $this->Request->uriSufixo();
    }
  }

  public function IsHealth(): bool {
    return ServerUtils::setDropBarAfter($this->Request->getApiBase()) 
       === ServerUtils::setDropBarAfter($this->Request->getRequestUri());
  }

  public function CreateControllersRoutersFilter(): void
  {
    $this->Controllers = Utils::Map(
      $this->Controllers, 
        fn($Controller) => array_merge(
          $Controller, [ControllerStructure::RequestRoutes => Utils::Filter(
            $Controller[ControllerStructure::RequestRoutes], function($Route){
              return $this->FilterValidMethodType($Route)
                  && $this->FilterValidRoute($Route);
            }
          )]
        )
    );
  } 

  public function isRequestMethodOptions(): void
  {
    if($this->Request->getRequestMethod() === HttpType::OPTIONS)
    {
      $this->Response->Send(
        HttpStatus::Ok
      );
    }
  }

  public function isRequestHealth(): void
  {
    if($this->IsHealth())
    {
      $this->Response->Send(
        ServerType::SERVER_RUNNING
      );
    }    
  }

  public function hasController(): bool {
    return sizeof($this->Controllers) !== 0;
  }

  public function isRequestHasController(): void {
    if ($this->hasController() === false) {
      $this->Response->Error(
        HttpError::NotFound()
      );
    }
  }

  public function hasRouteInController(): void {
    [ $Controller ] = $this->Controllers;
  }

  public function CreateControllersExecute(): void {
    $this->isRequestMethodOptions();
    $this->isRequestHealth();
    $this->isRequestHasController();
    
    if($this->hasController())
    {
      $this->hasRouteInController();
    }
  }  

  static public function create(array $modules = []): Application
  {
    return new static(
      $modules
    );
  }
}