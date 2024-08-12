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
  /**
   * @var string
   * **/  
  private string $requestMethod;

  /**
   * @var string
   * **/
  private string $requestUri;

  /**
   * @var string
   * **/
  private string $requestUriFull;
  
  /**
   * @var string
   * **/
  private string $requestRouteUri = "";

  /**
   * @var int
   * **/  
  private int $requestRouteUriLength = 0;

  /**
   * @var string
   * **/
  private string $apiBase;  

  /**
   * @var string
   * **/
  private string $apiVersion;

  /**
   * @var string
   * **/
  private string $controller;

  /**
   * @var bool
   * **/
  private bool $apiIsHealth = false;

  /**
   * @var string
   * **/
  private string $contentType;

  /**
   * @var int
   * **/
  private int $contentLength;

  /**
   * @var object<FileDataList>
   * **/  
  public FileDataList $fileDataList;

  /**
   * @var object<FieldDataList>
   * **/ 
  public FieldDataList $fieldDataList;

  /**
   * @var string
   * **/   
  private string $requestParamsStr;

  /**
   * @var array
   * **/
  public array $requestParams = [];

  /**
   * @var string
   * **/   
  private string $requestQuerysStr;

  /**
   * @var array
   * **/
  public array $requestQuerys = [];

  /**
   * @var array
   * **/
  private array $controllerArr = [];

  /**
   * @Construct
   * 
   * Create Request Object
   * @param: <none>
   * **/
  public function __construct(
    private Application $application
  ){
    $this->setProperties();
    $this->setBodyArgs();
  }

  /**
   * @Create Static
   * 
   * Create Request Object
   * @param: <none>
   * @return object<Request>
   * **/
  public static function create(
    Application $application
  ): Request {
    return new static(
      $application
    );
  }

  /**
   * @SetProperties
   * 
   * Define method parameters post size, submission type, submission url format
   * @param: <none>
   * @return <void>
   * **/  
  private function setProperties(
  ): void {
    [ "REQUEST_METHOD" => $this->requestMethod,
      "REQUEST_URI" => $this->requestUri,
      "REQUEST_URI" => $this->requestUriFull,
    ] = $_SERVER;

    if(isset($_SERVER["CONTENT_LENGTH"]) && isset($_SERVER["CONTENT_TYPE"])){
      [ "CONTENT_LENGTH" => $this->contentLength, 
        "CONTENT_TYPE" => $this->contentType, 
      ] = $_SERVER;    
    } else {
      $this->contentType = ContentType::ApplicationJson;
      $this->contentLength = 0;
    }

    /**
     * Split request url with question mark,
     * checking if there is?
     * **/
    if (preg_match("/\?/", $this->requestUri)) {
      [ $this->requestUri, $this->requestQuerysStr ] = explode(
        "?", $this->requestUri
      );

      /**
       * Define string to var parameters
       * **/
      parse_str(
        $this->requestQuerysStr, 
        $this->requestQuerys
      );
    }

    /**
     * Check if there is a backslash at the end of the request
     * **/
    if (preg_match("/\/$/", $this->requestUri)) {
      $this->requestUri = preg_replace(
        "/\/$/", "", $this->requestUri
      );
    }    

    /**
     * Define controller string
     * **/
    if (sizeof(explode("/", $this->requestUri)) >= 4) {
      [, $this->apiBase, $this->apiVersion, $this->controller ] = explode(
        DIRECTORY_SEPARATOR_LINUX, $this->requestUri
      );

      /**
       * Define 
       * ***/
      if (sizeof(explode("/", $this->requestUri)) >= 5){
        [, $this->requestRouteUri ] = explode(
          $this->controller, $this->requestUri
        );

        $this->requestRouteUriLength = sizeof(explode(
          DIRECTORY_SEPARATOR_LINUX, preg_replace( "/^\//", "", $this->requestRouteUri )
        ));
      }
    } else {
      /**
       * Define $apiIsHealth is true 
       * **/
      $this->apiIsHealth = true;
    }

    /***
     * Correct sent post type
     * **/
    if (preg_match('/^multipart\/form-data/', $this->contentType)) {
      [ $this->contentType ] = explode(
        ";", $this->contentType
      );
    }
  }

  /**
   * @setBodyArgs
   * 
   * Define Variables Get, Posts, Params and Files
   * @param: <none>
   * @return <void>
   * **/  
  private function setBodyArgs(
  ): void {
    if ((int)$this->contentLength !== 0) {
      switch( $this->contentType ) {
        /**
         * Define var Appication JSON
         * **/
        case ContentType::ApplicationJson: $this->getBodyApplicationJSON();
          break;

        /**
         * Define var from Data
         * **/  
        case ContentType::MultipartFormData: $this->getBodyMultipartFormData();
          break;
        
        /**
         * Define var from UrlEncode
         * **/  
        case ContentType::XWwwFormUrlencoded: $this->getBodyFormUrlEncoded();
          break;
      };
    }
  }

   /**
   * @GetBodyApplicationJSON
   * 
   * Define Variables Get, Posts, Params and Files
   * @param: <none>
   * @return <void>
   * **/  
  private function getBodyApplicationJSON(
  ): void {
    /**
     * Definir JSON from php://input
     * **/
    $ApplicationJSON = json_decode(
      file_get_contents(
        MultipartFormDataAttrs::FormDataDefaultFile, true
      )
    );

    /**
     * Definir $FieldDataList from Object FieldDatalist
     * **/
    if ($ApplicationJSON){
      $this->fieldDataList = FieldDataList::create(
        (array)$ApplicationJSON
      );
    }
  }  

  /**
   * @GetBodyMultipartFormData
   * 
   * Define args and files from form data
   * @param: <none>
   * @return <void>
   * **/  
  private function getBodyMultipartFormData(
  ): void {
    if ($this->requestMethod !== RequestMethod::POST) {
      $MultipartFormData = MultipartFormData::LoadPHPInput();
    } else $MultipartFormData = MultipartFormData::LoadPost();

    if ($MultipartFormData) {
      $this->fileDataList = $MultipartFormData->getFiles();
      $this->fieldDataList = $MultipartFormData->getFields();
    }    
  }

  /**
   * @GetBodyFormUrlEncoded
   * 
   * Define args and files from form data
   * @param: <none>
   * @return <void>
   * **/ 
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

  /**
   * @IsControllerRouteValid
   * 
   * List all controls and define them
   * @param: <array> $controllers
   * @return <void>
   * **/  
  private function isControllerRouteValid(
    string $routeUri,
     array $routeUriArr = [],
     array $requestUriArr = [],
     array $routeUriPaths = [],
  ): bool {
    $routeUriArr = explode(DIRECTORY_SEPARATOR_LINUX, $routeUri);
    $requestUriArr = explode(DIRECTORY_SEPARATOR_LINUX, $this->requestUri);

    /**
     * Route not exactly exactly the same
     * **/
    if (preg_match("/\:/", $routeUri)) {
      $routeUriPaths = Utils::MapKey($routeUriArr, fn($path, $key) => (
        $path === $requestUriArr[$key] || preg_match("/\:/", $path)
      ));

      return in_array(
        false, $routeUriPaths
      ) === false;
    } else {

      /**
       * Exactly the same route
       * **/
      $routeUriPaths = Utils::MapKey($routeUriArr, fn($path, $key) => (
        $path === $requestUriArr[$key]
      ));    

      return in_array(
        false, $routeUriPaths
      ) === false;
    }
  }

  /**
   * @GetParamsFromRequest
   * 
   * List all controls and define them
   * @param: <array> $controllers
   * @return <void>
   * **/  
  private function getParamsFromRequest(
    string $routeUri,
     array $routeUriArr = [],
     array $requestUriArr = []
  ): void {
    $routeUriArr = explode(DIRECTORY_SEPARATOR_LINUX, $routeUri);
    $requestUriArr = explode(DIRECTORY_SEPARATOR_LINUX, $this->requestUri);

    /**
     * Route not exactly exactly the same
     * **/
    foreach($routeUriArr as $key => $path) {
      if(preg_match("/\:/", $path)){
        $this->requestParams[
          preg_replace("/^\:/", "", $path)
        ] = $requestUriArr[$key];
      }
    }
  }  

  /**
   * @ExecuteMiddleware
   * 
   * Run Middleware Listing
   * @param: <array> $controllers
   * @return <void>
   * **/  
  public function executeMiddleware(
    array $middlewares = []
  ): void {
    if (sizeof($middlewares) !== 0) {
      Utils::Map($middlewares, function(array $middleware){
        /**
         * Create Instance from middleware
         * **/
        $middlewareInstance = call_user_func_array([
          new ReflectionClass($middleware[MiddlewareStructure::InstanceClass]), ConstructStructure::MethodNewInstance
        ], $middleware[MiddlewareStructure::ArgsClass]);

        /**
         * call methodo Execute from middleware 
         * **/
        $middlewareInstance->Execute(
          $this->application->request,
          $this->application->response
        );
      });
    }
  }

  /**
   * @SetControllers
   * 
   * List all controls and define them
   * @param: <array> $controllers
   * @return <void>
   * **/
  public function setControllers(
    array $controllers = []
  ): void {
    /**
     * You should populate the list of controllers only if a controller exists in the URL
     * **/
    if($this->apiIsHealth === false){
      /**
       * Start Loop
       * **/
      Utils::Map($controllers, function(string $controller){
        /**
         * Create RequestController Object
         * **/
        $RquestController = RequestController::create(
          controller: $controller
        );

        /**
         * Popular list of controls within the request
         * **/
        $this->controllerArr[ $RquestController->requestControllerItem->controllerName ] = RequestController::create(
          controller: $controller
        );
      });

      /**
       * Check if the controller exists
       * **/
      if (isset($this->controllerArr[$this->controller]) === false){
        $this->application->response->Error(
          "Cannot {$this->requestMethod} {$this->requestUriFull}", HttpStatus::NotFound
        );
      } else {

        /**
         * Check if a route with the same post method exists
         * **/
        $preFiltersRouters = Utils::Filter($this->controllerArr[$this->controller]->requestControllerItem->routerList->routers, function(RequestControllerRouterItem $requestControllerRouterItem) {
          [, $route] = explode($this->controller, $requestControllerRouterItem->routeUri);

          return $requestControllerRouterItem->routeMethodType === $this->requestMethod && $this->requestRouteUriLength === (
            empty( preg_replace( "/^\//", "", $route )) ? 0 : sizeof(
              explode(DIRECTORY_SEPARATOR_LINUX, preg_replace( "/^\//", "", $route ))
            )) && $this->isControllerRouteValid($requestControllerRouterItem->routeUri);
        });

        /**
         * Check if the route exists
         * **/
        if (sizeof($preFiltersRouters) === 0) {
          $this->application->response->Error(
            "Cannot {$this->requestMethod} {$this->requestUriFull}", HttpStatus::NotFound
          );
        } else {
          
          /**
           * Run Middleware list from controllers
           * **/
          $this->executeMiddleware($this->controllerArr[$this->controller]->requestControllerItem->controllerMiddlewares);

          /**
           * Create instance of the controller class
           * **/
          $controllerClass = call_user_func_array([
            new ReflectionClass($this->controllerArr[$this->controller]->requestControllerItem->controller), ConstructStructure::MethodNewInstance
          ], Utils::Map($this->controllerArr[$this->controller]->requestControllerItem->controllerConstruct[ConstructStructure::MethodParameters], fn($parameter) => (
            new $parameter()
          )));

          /**
           * Pesquisar por routas exatamente iguais 
           * **/
          [ $requestControllerRouterItem ] = $preFiltersRouters;

          /**
           * Verificar se exists requestControllerRouterItem
           * **/
          if( $requestControllerRouterItem instanceof RequestControllerRouterItem ){
            /**
             * Define var from params
             * **/  
            $this->getParamsFromRequest($requestControllerRouterItem->routeUri);

            /**
             * Run Middleware list from router
             * **/
            $this->executeMiddleware($requestControllerRouterItem->routeMiddleware);

            /**
             * Execute method from routers
             * **/            
            $resultControllerMethod = call_user_func_array([
              $controllerClass, $requestControllerRouterItem->routeName
            ], Utils::Map($requestControllerRouterItem->routeParameters, fn($parameter) => (
              (new $parameter())->Execute(
                $this->application->request,
                $this->application->response
              )
            )));

            /**
             * Check if there are errors
             * **/
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
    }
  }
}