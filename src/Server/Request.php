<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionClass;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Enums\ConstructStructure;
use Websyspro\HttpRequest\Enums\ContentType;
use Websyspro\HttpRequest\Enums\HttpStatus;
use Websyspro\HttpRequest\Enums\MiddlewareStructure;
use Websyspro\HttpRequest\Enums\MultipartFormDataAttrs;
use Websyspro\HttpRequest\Enums\RequestMethod;

class Request
{
  private string $requestMethod;
  private string $requestUri;
  private string $requestUriFull;
  private string $requestRouteUri = "";
  private int $requestRouteUriLength = 0;
  private string $apiBase;  
  private string $apiVersion;
  private string $controller;
  private bool $apiIsHealth = false;
  private string $contentType;
  private int $contentLength;
  public FileDataList $fileDataList;
  public FieldDataList $fieldDataList;
  private string $requestParamsStr;
  public array $requestParams = [];
  private string $requestQuerysStr;
  public array $requestQuerys = [];
  private array $controllerArr = [];

  public function __construct(
    public Application $application
  ){
    $this->setProperties();
    $this->setBodyArgs();
  }

  public static function create(
    Application $application
  ): Request {
    return new static (
      $application
    );
  }

  private function setProperties(
  ): void {
    [ "REQUEST_METHOD" => $this->requestMethod,
      "REQUEST_URI" => $this->requestUri,
      "REQUEST_URI" => $this->requestUriFull ] = $_SERVER;

    if (isset($_SERVER["CONTENT_LENGTH"]) && isset($_SERVER["CONTENT_TYPE"]) ){
      [ "CONTENT_LENGTH" => $this->contentLength, 
        "CONTENT_TYPE" => $this->contentType ] = $_SERVER;    
    } else {
      $this->contentType = ContentType::ApplicationJson;
      $this->contentLength = 0;
    }

    if (preg_match("/\?/", $this->requestUri)) {
      [ $this->requestUri, $this->requestQuerysStr ] = explode(
        "?", $this->requestUri
      );

      parse_str(
        $this->requestQuerysStr, 
        $this->requestQuerys
      );
    }

    if (preg_match("/\/$/", $this->requestUri)) {
      $this->requestUri = preg_replace(
        "/\/$/", "", $this->requestUri
      );
    }    

    if (sizeof(explode("/", $this->requestUri)) >= 4) {
      [ ,$this->apiBase ,$this->apiVersion ,$this->controller ] = explode(
        "/", $this->requestUri
      );

      if (sizeof(explode("/", $this->requestUri)) >= 5) {
        [ ,$this->requestRouteUri ] = explode(
          $this->controller, $this->requestUri
        );

        $this->requestRouteUriLength = sizeof( explode(
          "/", preg_replace( "/^\//", "", $this->requestRouteUri )
        ));
      }
    } else {
      $this->apiIsHealth = true;
    }

    if (preg_match('/^multipart\/form-data/', $this->contentType)) {
      [ $this->contentType ] = explode(
        ";", $this->contentType
      );
    }
  }

  private function setBodyArgs(
  ): void {
    if ($this->contentLength !== 0) {
      switch ($this->contentType) {
        case ContentType::ApplicationJson: $this->getBodyApplicationJSON();
          break;
        case ContentType::MultipartFormData: $this->getBodyMultipartFormData();
          break;
        case ContentType::XWwwFormUrlencoded: $this->getBodyFormUrlEncoded();
          break;
      };
    }
  }

  private function getBodyApplicationJSON(
  ): void {
    $ApplicationJSON = json_decode (
      file_get_contents(
        MultipartFormDataAttrs::FormDataDefaultFile, true
      )
    );

    if ($ApplicationJSON) {
      $this->fieldDataList = FieldDataList::create(
        (array)$ApplicationJSON
      );
    }
  }  

  private function getBodyMultipartFormData(
  ): void {
    $MultipartFormData = $this->requestMethod !== RequestMethod::POST
      ? MultipartFormData::LoadPHPInput()
      : MultipartFormData::LoadPost();

    if ($MultipartFormData) {
      $this->fileDataList = $MultipartFormData->getFiles();
      $this->fieldDataList = $MultipartFormData->getFields();
    }    
  }

  private function getBodyFormUrlEncoded(): void {
    parse_str( file_get_contents(
      MultipartFormDataAttrs::FormDataDefaultFile, true 
    ), $formUrlEncoded);
    
    if ($formUrlEncoded) {
      $this->fieldDataList = FieldDataList::create(
        $formUrlEncoded
      );
    }
  }

  private function isControllerRouteValid(
    string $routeUri
  ): bool {
    $routeUriArr = explode( "/", $routeUri );
    $requestUriArr = explode( "/", $this->requestUri );

    if (preg_match("/\:/", $routeUri)) {
      $routeUriPaths = Utils::MapKey($routeUriArr, fn($path, $key) => (
        $path === $requestUriArr[$key] || preg_match("/\:/", $path)
      ));

      return in_array(
        false, $routeUriPaths
      ) === false;
    } else {
      $routeUriPaths = Utils::MapKey( $routeUriArr, fn($path, $key) => (
        $path === $requestUriArr[$key]
      ));    

      return in_array(
        false, $routeUriPaths
      ) === false;
    }
  }

  private function getParamsFromRequest(
    string $routeUri
  ): void {
    $routeUriArr = explode( "/", $routeUri );
    $requestUriArr = explode( "/", $this->requestUri );

    foreach($routeUriArr as $key => $path){
      if(preg_match("/\:/", $path)) {
        $this->requestParams[
          preg_replace("/^\:/", "", $path)
        ] = $requestUriArr[$key];
      }
    }
  }  

  public function executeMiddleware(
    array $middlewares = []
  ): void {
    if ( sizeof($middlewares) !== 0 ) {
      Utils::Map( $middlewares, function(array $middleware){
        $reflectionClass = new ReflectionClass(
          $middleware[ MiddlewareStructure::InstanceClass ]
        );

        $middlewareReflectionClass = call_user_func_array([
          $reflectionClass, ConstructStructure::MethodNewInstance
        ], $middleware[ MiddlewareStructure::ArgsClass ]);

        $middlewareReflectionClass->Execute(
          $this->application->request,
          $this->application->response
        );
      });
    }
  }

  public function setControllers(
    array $controllers = []
  ): void {
    if($this->apiIsHealth === false){
      Utils::Map( $controllers, function(string $controller){
        $RquestController = RequestController::create(
          controller: $controller,
          request: $this
        );

        $this->controllerArr[
          $RquestController->requestControllerItem->controllerName
        ] = $RquestController;
      });

      if (isset($this->controllerArr[$this->controller]) === false) {
        $this->application->response->Error(
          "Cannot {$this->requestMethod} {$this->requestUriFull}", HttpStatus::NotFound
        );
      } else {
        $routersArr = $this->controllerArr[
          $this->controller
        ]->requestControllerItem;

        $preFiltersRouters = Utils::Filter($routersArr->routerList->routers, function(
          RequestControllerRouterItem $requestControllerRouterItem
        ): bool {
          [ , $route ] = explode(
            sprintf( "%s/%s/%s", 
              $this->apiBase, 
              $this->apiVersion,
              $this->controller
            ), $requestControllerRouterItem->routeUri
          );

          return $requestControllerRouterItem->routeMethodType === $this->requestMethod && $this->requestRouteUriLength === (
            empty( preg_replace( "/^\//", "", $route )) ? 0 : sizeof(
              explode( "/", preg_replace( "/^\//", "", $route ))
            )) && $this->isControllerRouteValid(
              preg_replace("/\/$/", "", $requestControllerRouterItem->routeUri)
            );
        });

        if (sizeof($preFiltersRouters) === 0){
          $this->application->response->Error(
            "Cannot route {$this->requestMethod} {$this->requestUriFull}", HttpStatus::NotFound
          );
        } else {
          $this->executeMiddleware(
            $routersArr->controllerMiddlewares
          );

          $controllerClass = call_user_func_array([
            new ReflectionClass($routersArr->controller), ConstructStructure::MethodNewInstance
          ], Utils::Map($routersArr->controllerConstruct[ ConstructStructure::MethodParameters ], fn($parameter) => new $parameter()));

          [ $requestControllerRouterItem ] = $preFiltersRouters;

          if( $requestControllerRouterItem instanceof RequestControllerRouterItem ) {
            $this->getParamsFromRequest($requestControllerRouterItem->routeUri);
            $this->executeMiddleware($requestControllerRouterItem->routeMiddleware);

            $resultControllerMethod = call_user_func_array([
              $controllerClass, $requestControllerRouterItem->routeName
            ], Utils::Map($requestControllerRouterItem->routeParameters, fn($parameter) => (
              (new $parameter())->Execute(
                $this->application->request,
                $this->application->response
              )
            )));

            if ($resultControllerMethod instanceof HttpError){
              $this->application->response->Error(
                $resultControllerMethod->ExceptionText,
                $resultControllerMethod->ExceptionCode
              );
            } else {
              $this->application->response->Send(
                $resultControllerMethod
              );
            }
          }
        }
      }
    } else {
      $this->application->response->Send(
        "Server started successfully"
      );
    }
  }
}