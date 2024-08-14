<?php

namespace Websyspro\HttpRequest\Decorations;

use Attribute;
use Websyspro\HttpRequest\Enums\Decoration;
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
    private int $fiesize = 0
  ){}

  public function Execute(
    Request $request,
    Response $response
  ): void {
    // $response->Error("Middleware Authorize -> {$this->filename}", HttpStatus::BadRequest);
  }
}