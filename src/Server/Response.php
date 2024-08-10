<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\Header;
use Websyspro\Core\Enums\HttpStatus;

class Response
{
  public const tokenSuccess = "success";
  public const tokenContent = "content";

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
    exit(json_encode([
      Response::tokenSuccess => true,
      Response::tokenContent => $Content
    ]));
  }

  public function sendError(
    mixed $HttpText,
    int $HttpError
  ): void {
    $this->HeaderJSON();
    $this->HeaderError($HttpError);
    exit(json_encode([
      Response::tokenSuccess => false,
      Response::tokenContent => $HttpText
    ]));
  }  
  
  public static function create(): Response
  {
    return new static();
  }
}