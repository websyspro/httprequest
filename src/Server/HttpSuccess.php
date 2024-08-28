<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Enums\HttpStatus;
use Websyspro\HttpRequest\Enums\HttpTypeSuccess;

class HttpSuccess
{
  public function __construct(
    public string $Text,
    public string $Code
  ){}

  public static function Created(
    string $text = HttpTypeSuccess::Created,
       int $code = HttpStatus::Created
  ): HttpSuccess {
    return new static(
      $text, $code
    );
  }
  
  public static function Updated(
    string $text = HttpTypeSuccess::Updated,
       int $code = HttpStatus::Created
  ): HttpSuccess {
    return new static(
      $text, $code
    );
  }
  
  public static function Deleted(
    string $ExceptionText = HttpTypeSuccess::Deleted,
       int $ExceptionCode = HttpStatus::Created
  ): HttpSuccess {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  
}