<?php

namespace Wsp\Api\Controllers;

use Websyspro\HttpRequest\Decorations\Authorize;
use Websyspro\HttpRequest\Decorations\Controller;
use Websyspro\HttpRequest\Decorations\FileValidate;
use Websyspro\HttpRequest\Decorations\Http\HttpDelete;
use Websyspro\HttpRequest\Decorations\Http\HttpGet;
use Websyspro\HttpRequest\Decorations\Http\HttpPost;
use Websyspro\HttpRequest\Decorations\Http\HttpPut;
use Websyspro\HttpRequest\Decorations\Parametros\Body;
use Websyspro\HttpRequest\Decorations\Parametros\Files;
use Websyspro\HttpRequest\Decorations\Parametros\Param;
use Websyspro\HttpRequest\Decorations\Parametros\Query;

#[Controller("user")]
class UserController
{
  function __construct(){}

  #[Authorize()]
  #[HttpPost("")]
  function createUser(
    #[Body()] array $body
  ): array {
    return $body;
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
    return ["test"];
  }

  #[Authorize()]
  #[FileValidate(
    extensions: ["pdf", "mp4"],
    filesize: 1024
  )]  
  #[HttpPut(":email")]
  function updateUserEmailTest(
    #[Body()] array $body,
    #[Query()] array $query,
    #[Files()] array $files,
    #[Param()] array $param
  ): mixed {
    return $files;
  }  

  #[Authorize()]  
  #[HttpPost(":email")]
  function updateUserEmail(
    #[Body()] array $body,
    #[Query()] array $query,
    #[Files()] array $files,
    #[Param()] array $param
  ): mixed {
    return $files;
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