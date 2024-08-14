<?php

use Websyspro\HttpRequest\Decorations\Authorize;
use Websyspro\HttpRequest\Decorations\Columns\Varchar;
use Websyspro\HttpRequest\Decorations\Controller;
use Websyspro\HttpRequest\Decorations\FileValidate;
use Websyspro\HttpRequest\Decorations\Http\HttpDelete;
use Websyspro\HttpRequest\Decorations\Http\HttpGet;
use Websyspro\HttpRequest\Decorations\Http\HttpPost;
use Websyspro\HttpRequest\Decorations\Http\HttpPut;
use Websyspro\HttpRequest\Decorations\Model;
use Websyspro\HttpRequest\Decorations\Parametros\Body;
use Websyspro\HttpRequest\Decorations\Parametros\Files;
use Websyspro\HttpRequest\Decorations\Parametros\Param;
use Websyspro\HttpRequest\Decorations\Parametros\Query;
use Websyspro\HttpRequest\Server\Application;

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

Application::create(
  apiBase: "api/v1",
  apiPort: "8080",
  modules: [],
  controllers: [
    UserController::class,
    ClientController::class
  ]
);