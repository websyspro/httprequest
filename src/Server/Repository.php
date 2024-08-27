<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Server\Drivers\DB;

class Repository
{
  function __construct(
    private string $entity
  ){}

  static function set(
    string $entity
  ): Repository {
    return new static(
      entity: $entity
    );
  }

  function getEntity(): string {
    $fullEntity = Utils::ArrayLastValue(
      Utils::Split(
        texto: $this->entity,
        separetor: "\\"
      )
    );

    return preg_replace(
      "/Entity$/", "", $fullEntity
    );
  }

  function findUnique(): array {
    return [];
  }

  function findFirst(): array {
    return DB::query(
      "select * 
         from {$this->getEntity()}
     order by Id desc limit 1"
    )->rows();
  }

  function findMany(
    array $where = [],
    array $orderBy = [],
    array $groupBy = [],
      int $rowsPerPage = 0,
      int $page = 1
  ): array {
    return DB::query(
      "select * 
         from {$this->getEntity()}"
    )->rows();
  }

  function create(array $dataArr = []): array {
    $dataArr = array_merge(
      $dataArr, [
        "Actived" => 1,
        "ActivedBy" => 1,
        "ActivedAt" => date("Y-m-d H:i:s"),
        "CreatedBy" => 1,
        "CreatedAt" => date("Y-m-d H:i:s"),
        "UpdatedBy" => 1,
        "UpdatedAt" => date("Y-m-d H:i:s"),
        "DeletedBy" => 1,
        "DeletedAt" => date("Y-m-d H:i:s"),        
      ]
    );

    $db = DB::query( sprintf(
      "insert into {$this->getEntity()} (%s) values(%s)", 
        Utils::Join( Utils::MapKey(array_keys($dataArr), fn($key) => "`{$key}`")),
        Utils::Join( Utils::MapKey($dataArr, fn($key) => "'{$key}'"))
    ));

    if ($db->hasError()){
      return [ $db->getError() ];
    } else return $this->findFirst();
  }

  function update(array $dataArr = []): array {
    return [];
  }

  function delete(array $dataArr = []): array {
    return [];
  }

  function createMany(array $dataManyArr = []): array {
    return [];
  }
 
  function updateMany(array $dataManyArr = []): array {
    return [];
  }
  
  function deleteMany(array $dataManyArr = []): array {
    return [];
  }
  
  function count(): int {
    return 0;
  }

  function where(): Repository {
    return $this;
  }

  function orderBy(): Repository {
    return $this;
  }
}