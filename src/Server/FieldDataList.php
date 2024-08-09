<?php

namespace Websyspro\Core\Server;

class FieldDataList
{
  public function __construct(
    public array $dataList = []
  ){}

  public static function create(array $fieldList = []): FieldDataList
  {
    return new static($fieldList);
  }
}