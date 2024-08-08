<?php

namespace Websyspro\Core\Server;

class App {
  public function __construct(
    array $modules = []
  ) {
    $this->listen();
  }

  static public function create(
    array $modules = []
  ): App {
    return new static(
      $modules
    );
  }

  public function listen(
  ): void {
    Request::create();
  }
}