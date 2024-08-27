<?php

namespace Websyspro\HttpRequest\Server;

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
    return "";
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
    return [];
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