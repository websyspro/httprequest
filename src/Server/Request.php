<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\ContentType;
use Websyspro\Core\Enums\MultipartFormDataAttrs;
use Websyspro\Core\Enums\RequestMethod;

class Request
{
  /**
   * @var string
   * **/  
  public string $requestMethod;

  /**
   * @var string
   * **/
  public string $requestUri;

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
  public string $controller;

  /**
   * @var bool
   * **/
  public bool $apiIsHealth = false;

  /**
   * @var string
   * **/
  public string $contentType;

  /**
   * @var int
   * **/
  public int $contentLength;

  /**
   * @var object<FileDataList>
   * **/  
  private FileDataList $FileDataList;

  /**
   * @var object<FieldDataList>
   * **/ 
  private FieldDataList $FieldDataList;

  /**
   * @var string
   * **/   
  private string $requestParamsStr;

  /**
   * @var array
   * **/
  private array $requestParams = [];

  /**
   * @Construct
   * 
   * Create Request Object
   * @param: <none>
   * **/
  public function __construct(
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
  ): Request {
    return new static();
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
    ] = $_SERVER;

    /**
     * Split request url with question mark,
     * checking if there is?
     * **/
    if (preg_match("/\?/", $this->requestUri)) {
      [ $this->requestUri, $this->requestParamsStr ] = explode(
        "?", $this->requestUri
      );

      /**
       * Define string to var parameters
       * **/
      parse_str(
        $this->requestParamsStr, 
        $this->requestParams
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