<?php

namespace Websyspro\HttpRequest\Decorations;

use Websyspro\HttpRequest\Enums\Decoration;
use Websyspro\HttpRequest\Server\Request;
use Websyspro\HttpRequest\Server\Response;
use Attribute;
use Websyspro\HttpRequest\Enums\HttpStatus;

#[Attribute(
  Attribute::TARGET_CLASS |
  Attribute::TARGET_METHOD
)]
class Authorize
{
  public const TypeDecoration = Decoration::Middleware;

  public function __construct(
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): void {
    //$response->Error("Middleware Authorize not", HttpStatus::BadRequest);
  }
}