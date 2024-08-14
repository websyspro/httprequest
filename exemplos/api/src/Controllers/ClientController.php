<?php

namespace Wsp\Api\Controllers;

use Websyspro\HttpRequest\Decorations\Controller;
use Websyspro\HttpRequest\Decorations\Http\HttpGet;

#[Controller("client")]
class ClientController
{
  function __construct(
    private string $ClientId
  ){}

  #[HttpGet("create")]
  function createCliente(): array {
    return [];
  }
}