<?php

namespace Websyspro\Core\Models\Decorations;

use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Server\Request;
use Websyspro\Core\Server\Response;
use Attribute;

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
    // Authentication::Use(
    //   $request,
    //   $response
    // )->TokenValid();
  }
}