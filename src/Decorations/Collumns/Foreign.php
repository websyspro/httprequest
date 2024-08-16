<?php

namespace Websyspro\HttpRequest\Decorations\Collumns;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Foreign
{
  public function __construct(
    private string $ReferenceModel,
    private string $ReferenceKey = "Id"
  ){}

  public function get(): array {
    return [
      "Foreign" => [
        "ReferenceModel" => $this->ReferenceModel,
        "ReferenceKey" => $this->ReferenceKey
      ] 
    ];
  }
}