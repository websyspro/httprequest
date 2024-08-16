<?php

require_once __DIR__ . "/vendor/autoload.php";

use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Decorations\Collumns\AutoInc;
use Websyspro\HttpRequest\Decorations\Collumns\BigInt;
use Websyspro\HttpRequest\Decorations\Collumns\Datetime;
use Websyspro\HttpRequest\Decorations\Collumns\TinyInt;
use Websyspro\HttpRequest\Decorations\Collumns\Varchar;
use Websyspro\HttpRequest\Server\Migrations;

class DefaultModel
{
  #[AutoInc()]
  public static string $Id;

  #[TinyInt()]
  public static string $Actived;

  #[BigInt()]
  public static string $ActivedBy;

  #[Datetime()]
  public static string $ActivedAt;
  
  #[BigInt()]
  public static string $CreatedBy;
  
  #[Datetime()]
  public static string $CreatedAt;
  
  #[BigInt()]
  public static string $UpdatedBy;
  
  #[Datetime()]
  public static string $UpdatedAt;
  
  #[BigInt()]
  public static string $DeletedBy;
  
  #[Datetime()]
  public static string $DeletedAt;
}


class UserModel 
extends DefaultModel
{
  #[Varchar(64)]
  private string $Username;
}

Migrations::create(
  models: [
    UserModel::class
  ]
);