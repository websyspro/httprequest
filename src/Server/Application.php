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
  private array $Controllers;
  private array $Models;

  public function __construct(
    private array $Modules = []
  ) {
    $this->CreateApp();
    $this->CreateControllers();
    $this->CreateControllersFilter();
    $this->CreateControllersRoutersFilter();
    $this->CreateControllersExecute();
  }

  public function CreateApp(): void {
    $this->Request = Request::create();
    $this->Response = Response::create();
  }

  public function CreateControllers(): void
  {
    $this->Controllers = Utils::Map(
      $this->Modules[Module::Controllers->name], fn($Controller) => [
        ControllerStructure::RequestController => ServerUtils::getControllerName($Controller),
        ControllerStructure::RequestApi => ServerUtils::GetControllerApi($Controller),
        ControllerStructure::RequestContruct => ServerUtils::GetConstruct($Controller),
        ControllerStructure::RequestMiddleware => ServerUtils::GetMiddlewares($Controller),
        ControllerStructure::RequestRoutes => ServerUtils::GetMethods($Controller),
      ]
    );
  }

  public function CreateControllersFilter(): void
  {
    [ , $apiBase, $apiVersion, $apiController ] = explode(
      DIRECTORY_SEPARATOR_LINUX, $this->Request->getRequestUri()
    );

    $this->Controllers = Utils::Filter(
      $this->Controllers, 
        fn($Controller) => $Controller[
          ControllerStructure::RequestApi
        ] === implode( DIRECTORY_SEPARATOR_LINUX, [
          $apiBase, $apiVersion, $apiController
        ])
    );
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
     $RouteRequest = explode(DIRECTORY_SEPARATOR_LINUX, $this->Request->UriSufixo());

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
            case "int":
              $this->Request->addParams($ParamString, (int)$RouteRequest[$Key]);
              break;
            case "string":
              $this->Request->addParams($ParamString, (string)$RouteRequest[$Key]);
              break;
            default:
              $this->Request->addParams($ParamString, (string)$RouteRequest[$Key]);
           }
         }
       });
       return true;
     } else return false;
   } else {
     return $Route[MethodStructure::MethodUri]
        === $this->Request->UriSufixo();
    }
  } 
  
  public function IsHealth(
  ): bool {
    return implode(
      ServerUtils::GetApiBarSep(), [ 
        ServerUtils::GetApiBase()
      ]
    ) === $this->Request->getRequestUri();
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

  public function CreateControllersExecute(): void
  {
    if ($this->Request->getRequestMethod() === HttpType::OPTIONS)
    {
      $this->Response->Send(
        HttpStatus::Ok
      );
    }

    if ($this->IsHealth()) {
      $this->Response->Send(
        ServerType::SERVER_RUNNING
      );
    }
  }  

  static public function create(array $modules = []): Application
  {
    return new static(
      $modules
    );
  }
}