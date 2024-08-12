<?php

use Websyspro\Core\Enums\Module;
use Websyspro\Core\Models\Decorations\Authorize;
use Websyspro\Core\Models\Decorations\Columns\Varchar;
use Websyspro\Core\Models\Decorations\Controller;
use Websyspro\Core\Models\Decorations\FileValidate;
use Websyspro\Core\Models\Decorations\Http\HttpDelete;
use Websyspro\Core\Models\Decorations\Http\HttpGet;
use Websyspro\Core\Models\Decorations\Http\HttpPost;
use Websyspro\Core\Models\Decorations\Http\HttpPut;
use Websyspro\Core\Models\Decorations\Model;
use Websyspro\Core\Models\Decorations\Parametros\Body;
use Websyspro\Core\Models\Decorations\Parametros\Files;
use Websyspro\Core\Models\Decorations\Parametros\Param;
use Websyspro\Core\Models\Decorations\Parametros\Query;
use Websyspro\Core\Server\Application;
use Websyspro\Core\Server\HttpError;

#[Model()]
class UserModel {
  #[Varchar(255)]
  public string $id;
  public string $createAt;
}

class UserCurrent {

}

#[FileValidate("test.jpg")]
#[Controller("user")]
class UserController
{
  function __construct(
    private UserCurrent $userCurrent
  ){}

  #[Authorize()]
  #[HttpPost("")]
  function createUser(
    #[Body()] array $body
  ): array {
    return [];
  } 

  #[Authorize()]
  #[HttpPut("account-id")]
  function accountId(
    #[Body()] array $body
  ): array {
    return [];
  }  

  #[Authorize()]
  #[HttpGet("email/exists/:email")]
  function emailExists(
    #[Body()] array $body,
    #[Param()] array $param
  ): array {
    return $param;
  }

  #[Authorize()]
  #[HttpGet()]
  function getAll(
  ): array {
    return [];
  }

  #[Authorize()]  
  #[HttpPut(":email")]
  function updateUserEmail(
    #[Body()] array $body,
    #[Query()] array $query,
    #[Files()] array $files
  ): mixed {
    return $body;
  }

  #[Authorize()]
  #[HttpPut()]
  function updateUser(
  ): array {
    return [];
  }

  #[Authorize()]
  #[HttpDelete()]
  function deleteUser(
  ): array {
    return [];
  }

  #[Authorize()]
  #[HttpGet("id/:id")]
  function findUserId(
  ): array {
    return [];
  }
}

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

Application::create([
  Module::Controllers => [
    UserController::class,
    ClientController::class
  ],
  Module::Models => [
    UserModel::class
  ]
]);