<?php

namespace Websyspro\HttpRequest\Decorations;

use Attribute;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Enums\HttpStatus;
use Websyspro\HttpRequest\Server\Request;
use Websyspro\HttpRequest\Server\Response;

#[Attribute(
  Attribute::TARGET_CLASS |
  Attribute::TARGET_METHOD
)]
class FileValidate {
  public const TypeDecoration = Decoration::Middleware;

  public function __construct(
    private array $extensions = [],
    private int $filesize = 0
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): void {
    Utils::Map($request->fileDataList->dataList, function($file) use($response) {
      if(in_array($file->type, $this->extensions) === false){
        return $response->Error(
          "The uploaded file type (.{$file->type}) is not allowed", HttpStatus::BadRequest
        );
      }

      if(bcdiv((float)$file->size, 1000, 2) > $this->filesize){
        return $response->Error(
          sprintf( "Uploaded file size (%smb) is not allowed", number_format(bcdiv((float)$file->size, 1000, 2)), 2, ".", ","), HttpStatus::BadRequest
        );
      }
    });
  }
}