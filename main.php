<?php

use Websyspro\Core\Enums\Module;
use Websyspro\Core\Models\Decorations\Columns\Varchar;
use Websyspro\Core\Models\Decorations\Controller;
use Websyspro\Core\Models\Decorations\Model;
use Websyspro\Core\Server\Application;

#[Model()]
class UserModel {
  #[Varchar(255)]
  public string $id;
  public string $createAt;
}

#[Controller("user")]
class UserController
{
  function __construct(){}
}

print_r(
  new ReflectionClass(UserController::class)
);

Application::create([
  Module::Controllers->name => [
    UserController::class
  ],
  Module::Models->name => [
    UserModel::class
  ]
]);