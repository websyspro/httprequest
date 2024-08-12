<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ContentType;
use Websyspro\Core\Enums\HttpStatus;
use Websyspro\Core\Enums\MultipartFormDataAttrs;
use Websyspro\Core\Enums\RequestMethod;

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
  private FileDataList $FileDataList;

  /**
   * @var object<FieldDataList>
   * **/ 
  private FieldDataList $FieldDataList;

  /**
   * @var object<FieldDataList>
   * **/  
  private FieldDataList $QueryDataList;

  /**
   * @var string
   * **/   
  private string $requestParamsStr;

  /**
   * @var array
   * **/
  private array $requestParams = [];

  /**
   * @var string
   * **/   
  private string $requestQuerysStr;

  /**
   * @var array
   * **/
  private array $requestQuerys = [];

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
      "CONTENT_LENGTH" => $this->contentLength,  
      "CONTENT_TYPE" => $this->contentType,
      "REQUEST_URI" => $this->requestUri,
      "REQUEST_URI" => $this->requestUriFull,
    ] = $_SERVER;

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
      $this->FieldDataList = FieldDataList::create(
        $ApplicationJSON
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
      $this->FileDataList = $MultipartFormData->getFiles();
      $this->FieldDataList = $MultipartFormData->getFields();
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
      $this->FieldDataList = FieldDataList::create(
        $formUrlEncoded
      );
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
            )
          );
        });

        /**
         * Check if the route exists
         * **/
        if (sizeof($preFiltersRouters) === 0) {
          $this->application->response->Error(
            "Cannot {$this->requestMethod} {$this->requestUriFull}", HttpStatus::NotFound
          );
        }
      }
    }
  }

  public function uriSufixo(
    array $ArrayList = []
  ): string {
    $ArrayList = array_slice( explode(
      DIRECTORY_SEPARATOR_LINUX,
      $_SERVER[ "PATH_INFO" ]
    ), 4 );

    return implode(
      DIRECTORY_SEPARATOR_LINUX,
      $ArrayList
    );
  }

  public function getApiBase(
  ): string {
    return implode( "", [
      DIRECTORY_SEPARATOR_LINUX,
      API_BASE,
      DIRECTORY_SEPARATOR_LINUX
    ]);
  }
  
  public function getRequestMethod(): string 
  {
    return $this->requestMethod;
  }

  public function getRequestUri(): string
  {
    return $this->requestUri;
  }

  public function getFileContents(): mixed
  {
    return file_get_contents(
      MultipartFormDataAttrs::FormDataDefaultFile, true 
    );
  }  










  public function setBody(
    FieldDataList $fieldDataList = null
  ): void {
    $this->FieldDataList = $fieldDataList;
  }

  public function setFiles(
    FileDataList $fileDataList = null
  ): void {
    $this->FileDataList = $fileDataList;
  }

  public function getFiles(): mixed {
    return $this->FileDataList->dataList;
  }
}