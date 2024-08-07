<?php

use Websyspro\Core\Enums\Module;
use Websyspro\Core\Models\Decorations\Columns\Varchar;
use Websyspro\Core\Models\Decorations\Model;
use Websyspro\Core\Server\App;

#[Model()]
class UserModel {
  #[Varchar(255)]
  public $Id;
}

App::create([
  Module::Models->value => [
    UserModel::class
  ]
]);