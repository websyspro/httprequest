<?php

use Websyspro\Core\Enums\Module;
use Websyspro\Core\Models\Decorations\Columns\Varchar;
use Websyspro\Core\Models\Decorations\Model;
use Websyspro\Core\Server\Application;

#[Model()]
class UserModel {
  #[Varchar(255)]
  public string $id;
  public string $createAt;
}

Application::create([
  Module::Models->name => [
    UserModel::class
  ]
]);