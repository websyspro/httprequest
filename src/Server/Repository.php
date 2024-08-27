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

  private function SelectFindMany(
    array $Select = []
  ):  string {
    return is_array($Select) && sizeof($Select)
      ? Utils::Join(
          Utils::Map(
            $Select, fn(string $key) => "`{$key}`")
          )
      : "*";
  }

  private function WhereFindMany(
    array $Where = []
  ): string {
    if (is_array($Where) && sizeof($Where)) {
      $Where = Utils::Join(
        Utils::MapKey(
          $Where, fn(string $val, string $key) => "{$key}='{$val}'"
        )
      );

      return "where {$Where}";
    } else return "where 1=1";
  }

  public function GroupByFindMany(
    array $OrderBy = []
  ): string {
    return "";
  }  

  public function OrderByFindMany(
    array $OrderBy = []
  ): string {
    return "order by 1 desc";
  }

  private function ObterPage(
    int $Page, 
    int $RowsPerPage = 0
  ): string {
    return bcsub(
      $Page, 1, 0
    ) * $RowsPerPage;
  }

  private function PagedFindMany(
    int $RowsPerPage = 0,
    int $Page = 1
  ): string {
    if ( $RowsPerPage !== 0 && $Page !== 0) {
      return "limit {$this->ObterPage($Page, $RowsPerPage)}, {$RowsPerPage}";
    } return "";
  }  

  public function findMany(
      int $Id = 0,
    array $Select = [],
    array $Where = [],
    array $OrderBy = [],
    array $GroupBy = [],
      int $RowsPerPage = 0,
      int $Page = 1
  ): DB {
    $Where = array_merge(
      $Where, $Id !== 0 ? [ "Id" => $Id ] : []
    );

    return DB::query(
      "select {$this->SelectFindMany($Select)}
         from {$this->ObterEntity()}
              {$this->WhereFindMany($Where)}
              {$this->GroupByFindMany($GroupBy)}
              {$this->OrderByFindMany($OrderBy)}
              {$this->PagedFindMany($Page, $RowsPerPage)}"
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
    )->ObterRows();
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