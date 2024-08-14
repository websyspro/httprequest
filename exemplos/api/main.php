<?php

use Websyspro\HttpRequest\Server\Application;

use Wsp\Api\Controllers\UserController;
use Wsp\Api\Controllers\ClientController;

use Wsp\Api\Model\UserModel;

Application::create(
  apiBase: "api/v1",
  apiPort: "8080",
  modules: [
    UserModel::class
  ],
  controllers: [
    UserController::class,
    ClientController::class
  ]
);