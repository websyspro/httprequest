<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\MultipartFormDataAttrs;
use Websyspro\Core\Enums\RequestMethod;

class ServerUtils
{
  public static function getRequestMethod(): string 
  {
    return $_SERVER[ "REQUEST_METHOD" ];
  }

  public static function getFileContents(): mixed
  {
    return file_get_contents(
      MultipartFormDataAttrs::FormDataDefaultFile->value, true 
    );
  }  

  public static function getPostContent(
    Request $request
  ): void
  {
    $MultipartFormData = static::getRequestMethod() !== RequestMethod::POST->name
      ? MultipartFormData::LoadPHPInput()
      : MultipartFormData::LoadPost();

    if ($MultipartFormData)
    {
      $request->setFiles(
        $MultipartFormData->getFiles()
      );

      $request->setBody(
        $MultipartFormData->getFields()
      );
    }
  }

  public static function getPost(): array {
    return $_POST;
  }

  public static function getBodyApplicationJSON(): mixed {
    return json_decode( static::getFileContents());
  }

  public static function getBodyFormUrlEncoded(): mixed {
    parse_str( static::getFileContents(), $urlEncoded);
    return $urlEncoded;
  }

  public static function getBodyMultipartFormData(
    Request $request
  ): void {
    static::getPostContent(
      $request
    );
  }
}