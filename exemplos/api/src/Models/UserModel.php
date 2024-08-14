<?php

namespace Wsp\Api\Model;

use Websyspro\HttpRequest\Decorations\Columns\Varchar;
use Websyspro\HttpRequest\Decorations\Model;

#[Model()]
class UserModel {
  #[Varchar(255)]
  public string $id;
  public string $createAt;
}