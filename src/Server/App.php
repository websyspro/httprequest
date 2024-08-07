<?php

namespace Websyspro\Core\Server;

class App {
  public function __construct(
    private array $modules = []
  ) {
    
  }

  static public function create(
    array $modules = []
  ): App {
    return new static(
      modules: $modules
    );
  }
}