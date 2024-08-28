<?php

namespace Websyspro\HttpRequest\Server;

use Websyspro\HttpRequest\Enums\HttpStatus;
use Websyspro\HttpRequest\Enums\HttpTypeError;

class HttpError
{
  public function __construct(
    public string $Text,
    public string $Code
  ){
    $this->Text = $Text;
    $this->Code = $Code;
  }

  public static function NonAuthoritativeInformation(
    string $Text = HttpTypeError::NonAuthoritativeInformation,
       int $Code = HttpStatus::NonAuthoritativeInformation
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function BadRequest(
    string $Text = HttpTypeError::BadRequest,
       int $Code = HttpStatus::BadRequest
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }

  public static function Unauthorized(
    string $Text = HttpTypeError::Unauthorized,
       int $Code = HttpStatus::Unauthorized
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }

  public static function PaymentRequired(
    string $Text = HttpTypeError::PaymentRequired,
       int $Code = HttpStatus::PaymentRequired
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function Forbidden(
    string $Text = HttpTypeError::Forbidden,
       int $Code = HttpStatus::Forbidden
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }

  public static function NotFound(
    string $Text = HttpTypeError::NotFound,
       int $Code = HttpStatus::NotFound 
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function MethodNotAllowed(
    string $Text = HttpTypeError::MethodNotAllowed,
       int $Code = HttpStatus::MethodNotAllowed
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function NotAcceptable(
    string $Text = HttpTypeError::NotAcceptable,
       int $Code = HttpStatus::NotAcceptable    
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function ProxyAuthenticationRequired(
    string $Text = HttpTypeError::ProxyAuthenticationRequired,
       int $Code = HttpStatus::ProxyAuthenticationRequired
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  

  public static function RequestTimeout(
    string $Text = HttpTypeError::RequestTimeout,
       int $Code = HttpStatus::RequestTimeout
  ): HttpError {
    return new static(
      $Text,
      $Code
    );
  }  
}