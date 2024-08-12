<?php

namespace Websyspro\HttpRequest\Server;

class RouterList
{
  public array $routers = [];

  public function __construct(){}

  public function addRouter(
    mixed $router
  ): void {
    $this->routers[] = $router;
  }

  public static function create(): RouterList {
    return new static();
  }  
}