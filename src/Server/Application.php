<?php

namespace Websyspro\Core\Server;

class Application {
  public function __construct(
    array $modules = []
  ) {
    $this->listen();
  }

  static public function create(
    array $modules = []
  ): Application {
    return new static(
      $modules
    );
  }

  public function listen(
  ): void {
    Request::create();
  }
}