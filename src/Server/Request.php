<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\ContentType;
use Websyspro\Core\Enums\MultipartFormDataAttrs;
use Websyspro\Core\Enums\RequestMethod;

class Request
{
  private string $RequestMethod;
  private string $RequestUri;
  private string $ContentType;
  private int $ContentLength;
  private mixed $body;
  private mixed $params;
  private FileDataList $FileDataList;
  private FieldDataList $FieldDataList;

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

  public function DefineProperties(): void 
  {
    [ "REQUEST_METHOD" => $this->RequestMethod,
      "CONTENT_LENGTH" => $this->ContentLength,  
      "CONTENT_TYPE" => $this->ContentType,
      "REQUEST_URI" => $this->RequestUri,
    ] = $_SERVER;

    if (preg_match('/^multipart\/form-data/', $this->ContentType))
    {
      [ $this->ContentType ] = explode(
        ";", $this->ContentType
      );
    }
  }

  public function getRequestMethod(): string 
  {
    return $this->RequestMethod;
  }

  public function getRequestUri(): string
  {
    return $this->RequestUri;
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

  public function getBodyApplicationJSON(): void
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

  public function getBodyFormUrlEncoded(): void {
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
  {
    // print_r($this);
  }

  private function getBody(): mixed
  {
    return $this->FieldDataList->dataList;
  }

  private function getParams(): mixed
  {
    return $this->params;
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
}