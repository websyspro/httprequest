<?php

namespace Websyspro\Core\Server;

class FieldDataList
{
  public function __construct(
    public mixed $dataList = []
  ){}

  public static function create(mixed $fieldList = []): FieldDataList
  {
    return new static($fieldList);
  }
}