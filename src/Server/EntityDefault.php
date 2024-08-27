<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Decorations\Collumns\AutoInc;
use Websyspro\HttpRequest\Decorations\Collumns\BigInt;
use Websyspro\HttpRequest\Decorations\Collumns\Datetime;
use Websyspro\HttpRequest\Decorations\Collumns\Required;
use Websyspro\HttpRequest\Decorations\Collumns\TinyInt;

abstract class EntityDefault
{
  #[BigInt()]
  #[AutoInc()]
  #[Required()]
  public int $Id;

  #[TinyInt()]
  #[Required()]
  public int $Actived;

  #[BigInt()]
  #[Required()]
  public int $ActivedBy;

  #[Datetime()]
  #[Required()]
  public string $ActivedAt;

  #[BigInt()]
  #[Required()]
  public string $CreatedBy;
  
  #[Datetime()]
  #[Required()]
  public string $CreatedAt;
  
  #[BigInt()]
  public int $UpdatedBy;
  
  #[Datetime()]
  public string $UpdatedAt;
  
  #[BigInt()]
  public int $DeletedBy;
  
  #[Datetime()]
  public string $DeletedAt;  
}