<?php

namespace Websyspro\HttpRequest\Server;

use Websyspro\HttpRequest\Enums\HttpStatus;
use Websyspro\HttpRequest\Enums\HttpTypeError;

class HttpError
{
  public function __construct(
    public string $ExceptionText,
    public string $ExceptionCode
  ){
    $this->ExceptionText = $ExceptionText;
    $this->ExceptionCode = $ExceptionCode;
  }

  public static function NonAuthoritativeInformation(
    string $ExceptionText = HttpTypeError::NonAuthoritativeInformation,
       int $ExceptionCode = HttpStatus::NonAuthoritativeInformation
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function BadRequest(
    string $ExceptionText = HttpTypeError::BadRequest,
       int $ExceptionCode = HttpStatus::BadRequest
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }

  public static function Unauthorized(
    string $ExceptionText = HttpTypeError::Unauthorized,
       int $ExceptionCode = HttpStatus::Unauthorized
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }

  public static function PaymentRequired(
    string $ExceptionText = HttpTypeError::PaymentRequired,
       int $ExceptionCode = HttpStatus::PaymentRequired
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function Forbidden(
    string $ExceptionText = HttpTypeError::Forbidden,
       int $ExceptionCode = HttpStatus::Forbidden
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }

  public static function NotFound(
    string $ExceptionText = HttpTypeError::NotFound,
       int $ExceptionCode = HttpStatus::NotFound 
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function MethodNotAllowed(
    string $ExceptionText = HttpTypeError::MethodNotAllowed,
       int $ExceptionCode = HttpStatus::MethodNotAllowed
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function NotAcceptable(
    string $ExceptionText = HttpTypeError::NotAcceptable,
       int $ExceptionCode = HttpStatus::NotAcceptable    
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function ProxyAuthenticationRequired(
    string $ExceptionText = HttpTypeError::ProxyAuthenticationRequired,
       int $ExceptionCode = HttpStatus::ProxyAuthenticationRequired
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  

  public static function RequestTimeout(
    string $ExceptionText = HttpTypeError::RequestTimeout,
       int $ExceptionCode = HttpStatus::RequestTimeout
  ): HttpError {
    return new static(
      $ExceptionText,
      $ExceptionCode
    );
  }  
}