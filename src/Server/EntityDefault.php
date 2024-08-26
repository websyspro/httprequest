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
  #[Required()]
  public int $UpdatedBy;
  
  #[Datetime()]
  #[Required()]
  public string $UpdatedAt;
  
  #[BigInt()]
  #[Required()]
  public int $DeletedBy;
  
  #[Datetime()]
  #[Required()]
  public string $DeletedAt;  
}