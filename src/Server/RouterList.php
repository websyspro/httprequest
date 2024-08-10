<?php

namespace Websyspro\Core\Server;

class RouterList
{
  private array $Routers = [];

  public function __construct(){}

  public function addRouter(
    mixed $Router
  ): void {
    $this->Routers[] = $Router;
  }

  public static function create(): RouterList {
    return new static();
  }  
}