<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\Header;
use Websyspro\Core\Enums\HttpStatus;

class Response
{
  public const success = "success";
  public const content = "content";

  public function __construct(
    private Application $application
  ){}

  public function HeaderJSON(
  ): void {
    header(Header::AccessControlAllowOrigin);
    header(Header::AccessControlAllowHeaders);
    header(Header::AccessControlAllowMethods);
    header(Header::ApplicationJSON);    
  }

  public function HeaderStatusOk(
  ): void {
    http_response_code(
      HttpStatus::Ok
    );
  }

  public static function HeaderError(
    int $HttpError
  ): void {
    http_response_code(
      $HttpError
    );
  }   

  public function Send(
    mixed $Content
  ): void {
    $this->HeaderJSON();
    $this->HeaderStatusOk();

    exit (
      json_encode([
        Response::success => true,
        Response::content => $Content
      ])
    );
  }

  public function Error(
    mixed $HttpContext,
      int $HttpError = 0
  ): void {
    $this->HeaderJSON();
    
    if($HttpContext instanceof HttpError)
    {
      $this->HeaderError(
        $HttpContext->ExceptionCode
      );
      exit (
        json_encode([
          Response::success => false,
          Response::content => $HttpContext->ExceptionText
        ])
      );      
    } 
    else
    {
      $this->HeaderError(
        $HttpError
      );

      exit(json_encode([
        Response::success => false,
        Response::content => $HttpContext
      ]));
    }
  }  
  
  public static function create(
    Application $application
  ): Response {
    return new static(
      $application
    );
  }
}