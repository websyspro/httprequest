<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Common\Utils;

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
    return [];
  }

  function findMany(
    array $where = [],
    array $orderBy = [],
    array $groupBy = [],
      int $rowsPerPage = 0,
      int $page = 1
  ): array {
    return [ $this->getEntity() ];
  }

  function create(array $dataArr = []): array {
    return [];
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