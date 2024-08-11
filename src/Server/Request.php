<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\ContentType;
use Websyspro\Core\Enums\MultipartFormDataAttrs;
use Websyspro\Core\Enums\RequestMethod;

class Request
{
  public string $RequestMethod;
  public string $requestUri;
  public string $ContentType;
  public int $ContentLength;
  private mixed $params;
  private FileDataList $FileDataList;
  private FieldDataList $FieldDataList;
  private array $Params = [];

  public function __construct()
  {
    $this->DefineProperties();
    $this->DefineBodyArgs();
    $this->DefineParamsArgs();
  }

  public static function create(): Request
  {
    return new static();
  }

  private function DefineProperties(): void 
  {
    [ "REQUEST_METHOD" => $this->RequestMethod,
      "CONTENT_LENGTH" => $this->ContentLength,  
      "CONTENT_TYPE" => $this->ContentType,
      "REQUEST_URI" => $this->requestUri,
    ] = $_SERVER;

    if (preg_match('/^multipart\/form-data/', $this->ContentType))
    {
      [ $this->ContentType ] = explode(
        ";", $this->ContentType
      );
    }
  }

  private function uriSufixo(
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

  private function getApiBase(
  ): string {
    return implode( "", [
      DIRECTORY_SEPARATOR_LINUX,
      API_BASE,
      DIRECTORY_SEPARATOR_LINUX
    ]);
  }
  
  private function addParams(string $key, mixed $value): void {
    $this->Params[$key] = $value;
  }

  private function getParams(): FieldDataList {
    return FieldDataList::create(
      $this->Params
    );
  }

  public function getRequestMethod(): string 
  {
    return $this->RequestMethod;
  }

  public function getRequestUri(): string
  {
    return $this->requestUri;
  }

  public function getFileContents(): mixed
  {
    return file_get_contents(
      MultipartFormDataAttrs::FormDataDefaultFile->value, true 
    );
  }  

  private function DefineBodyArgs(): void 
  {
    if ($this->ContentLength !== 0)
    {
      switch( $this->ContentType )
      {
        case ContentType::ApplicationJson->value:
          $this->getBodyApplicationJSON();
            break;
        case ContentType::MultipartFormData->value:
          $this->getBodyMultipartFormData();
            break;
        case ContentType::XWwwFormUrlencoded->value:
          $this->getBodyFormUrlEncoded();
            break;
      };
    }
  }

  private function getBodyApplicationJSON(): void
  {
    $ApplicationJSON = json_decode(
      $this->getFileContents()
    );

    if ($ApplicationJSON)
    {
      $this->setBody(
        FieldDataList::create(
          $ApplicationJSON
        )
      );
    }    
  }

  private function getBodyMultipartFormData(): void {
    $MultipartFormData = $this->getRequestMethod() !== RequestMethod::POST->name
      ? MultipartFormData::LoadPHPInput()
      : MultipartFormData::LoadPost();

    if ($MultipartFormData)
    {
      $this->setFiles(
        $MultipartFormData->getFiles()
      );

      $this->setBody(
        $MultipartFormData->getFields()
      );
    }    
  }

  private function getBodyFormUrlEncoded(): void {
    parse_str( $this->getFileContents(), $FormUrlEncoded);
    
    if ($FormUrlEncoded)
    {
      $this->setBody(
        FieldDataList::create(
          $FormUrlEncoded
        )
      );
    }
  }

  private function DefineParamsArgs(): void
  {}

  private function getBody(): mixed
  {
    return $this->FieldDataList->dataList;
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