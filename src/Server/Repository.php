<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Server\Drivers\DB;

class Repository
{
  function __construct(
    private string $entity
  ){}

  public static function set(
    string $entity
  ): Repository {
    return new static(
      entity: $entity
    );
  }

  public function ObterEntity(): string {
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

  public function findUnique(): array {
    return [];
  }

  public function findFirst(): array {
    return DB::query(
      "select * 
         from {$this->ObterEntity()}
     order by Id desc limit 1"
    )->rows();
  }

  public function findMany(
    array $where = [],
    array $orderBy = [],
    array $groupBy = [],
      int $rowsPerPage = 0,
      int $page = 1
  ): array {
    return DB::query(
      "select * 
         from {$this->ObterEntity()}"
    )->rows();
  }

  private function ObterCreateDefaultValues(
    array $dataArr = []
  ): array {
    return array_merge(
      $dataArr, [
        "Actived"   => 1,
        "ActivedBy" => 1,
        "ActivedAt" => Utils::Date(),
        "CreatedBy" => 1,
        "CreatedAt" => Utils::Date(),        
      ]
    );
  }

  private function ObterCreatePropsNames(
    array $dataArr = []
  ): string {
    return Utils::Join(
      Utils::MapKey(
        array_keys(
          $dataArr
        ), fn($key) => "`{$key}`"
      )
    );
  }

  private function ObterCreatePropsValues(
    array $dataArr = []
  ): string {
    return Utils::Join(
      Utils::MapKey(
        $dataArr, fn($key) => "'{$key}'"
      )
    );    
  }

  public function create(array $dataArr = []): DB {
    $dataArr = $this->ObterCreateDefaultValues(
      $dataArr
    );
    
    return DB::query(
      "insert into {$this->ObterEntity()} 
        ({$this->ObterCreatePropsNames($dataArr)}) 
          values({$this->ObterCreatePropsValues($dataArr)})"
    );
  }

  public function update(array $dataArr = []): array {
    return [];
  }

  public function delete(array $dataArr = []): array {
    return [];
  }

  public function createMany(array $dataManyArr = []): array {
    return [];
  }
 
  public function updateMany(array $dataManyArr = []): array {
    return [];
  }
  
  public function deleteMany(array $dataManyArr = []): array {
    return [];
  }
  
  public function count(): int {
    return 0;
  }

  public function where(): Repository {
    return $this;
  }

  public function orderBy(): Repository {
    return $this;
  }
}