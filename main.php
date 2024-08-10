<?php

use Websyspro\Core\Enums\Module;
use Websyspro\Core\Models\Decorations\Authorize;
use Websyspro\Core\Models\Decorations\Columns\Varchar;
use Websyspro\Core\Models\Decorations\Controller;
use Websyspro\Core\Models\Decorations\Http\HttpPost;
use Websyspro\Core\Models\Decorations\Model;
use Websyspro\Core\Models\Decorations\Parametros\Body;
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
  function __construct(
    private string $UserId
  ){}

  #[Authorize()]
  #[HttpPost("")]
  function PostUser(
    #[Body()] array $body
  ): array {
    return [];
  }  

  #[Authorize()]
  #[HttpPost("create")]
  function CreateUser(
    #[Body()] array $body
  ): array {
    return [];
  }
}

Application::create([
  Module::Controllers => [
    UserController::class
  ],
  Module::Models => [
    UserModel::class
  ]
]);