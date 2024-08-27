<?php

namespace Websyspro\HttpRequest\Server;
use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Server\Drivers\DB;

class Repository
{
  private array $EntityProperties = [];

  function __construct(
    private string $Entity
  ){
    $this->defineEntityProperties();
  }

  private function defineEntityProperties(
  ): void {
    $this->EntityProperties = Utils::MapKey(
      Migrations::ObterEntityStructure($this->Entity),
        function(array $property, string $key){
          return [
            "type" => preg_replace("/[0-9]|\(|\)|(,)/", "", $property["type"]),
            "null" => isset($property["required"]) ? (
                $property["required"] === "sim" ? true : false 
              ) : false
          ];
        }
    );
  }

  public static function set(
    string $Entity
  ): Repository {
    return new static(
      Entity: $Entity
    );
  }

  public function ObterEntity(): string {
    $fullEntity = Utils::ArrayLastValue(
      Utils::Split(
        texto: $this->Entity,
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
    return Utils::Map( $dataArr, fn($data) => (
      array_merge(
        (array)$data, [
          "Actived"   => 1,
          "ActivedBy" => 1,
          "ActivedAt" => Utils::Date(),
          "CreatedBy" => 1,
          "CreatedAt" => Utils::Date(),        
        ]
      )
    ));
  }

  private function ObterCreatePropsNames(
    array $dataArr = []
  ): string {
    return Utils::Join(
      Utils::Map(
        array_keys($dataArr), fn(string $key) => "`{$key}`"
      )
    );
  }

  private function ObterCreatePropsValues(
    array $dataArr = []
  ): string {
    return Utils::Join( Utils::MapKey(
      $dataArr, fn($valueList) => "({$valueList})"
    ));    
  }

  private function ParseValue(
    string $val,
    string $key
  ): mixed {
    [ $type ] = array_values(
      $this->EntityProperties[$key]
    );

    if ( in_array($type, [ "datetime", "date" ])) {
      return preg_replace( 
        "/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", "'{$val}'"
      );
    } else 
    if ( in_array($type, [ "bigint", "smallint", "tinyint" ])) {
      return "{$val}";
    } else
    if ( in_array($type, [ "decimal" ])) {
      return preg_replace(
        [ "/\./i", "/\,/i" ], [ "", "." ], $val
      );
    } else return "'{$val}'";
  }

  private function ParsePropertiesValues(array $dataArr = []): array {
    return Utils::Map( $dataArr, fn(array $data) => (
      Utils::MapKey( $data, fn(string $val, string $key) => (
        $this->ParseValue($val, $key)
      ))
    ));
  }

  public function create(array $dataArr = []): null | DB {
    $ParsePropertiesValues = $this->ParsePropertiesValues(
      $this->ObterCreateDefaultValues(
        $dataArr
      )
    );

    if (is_array($ParsePropertiesValues) === false && sizeof($ParsePropertiesValues) === 0) {
      return null;
    }

    [ $properties ] = $ParsePropertiesValues;

    $propertiesValues = Utils::Map(
      $ParsePropertiesValues, fn(array $data) => Utils::Join(
        array_values($data)
      )
    );

    return DB::query(
      "insert into {$this->ObterEntity()} 
        ({$this->ObterCreatePropsNames($properties)}) 
          values{$this->ObterCreatePropsValues($propertiesValues)}"
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