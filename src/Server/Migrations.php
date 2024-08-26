<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionAttribute;
use ReflectionProperty;

use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Server\Drivers\DB;

class Migrations
{
  public array $entitysArr = [];
  public array $entitysPersisteds = [];
  public array $entitysCreateds = [];
  public array $entitysUpdates = [];
  public array $entitysInserts = [];
  public array $entitysDrops = [];
  public array $entitysCreatedsIndexes = [];
  public array $entitysCreatedsUniques = [];
  public array $entitysCreatedsForeigns = [];
  public array $scriptArr = [];

  public function __construct(
    private array $entitys = []
  ){
    if ($this->hasMigrations()) {
      $this->initMigrations();
    }
  }

  public function hasMigrations(
  ): bool {
    [ "argv" => $argv,
      "argc" => $argc ] = $_SERVER;

    if ( (int)$argc >= 2 ) {
      [ , $cmd ] = $argv;

      if (empty($cmd) === false) {
        [ $cmdStr, $cmdType ] = explode( "=", $cmd );

        if ($cmdStr === "migrations" && $cmdType === "yes") {
          return true;
        } else return false;
      } else return false;
    } else return false;
  }

  public function getEntityName(
    string $entity
  ): string {
    return preg_replace(
      "/Entity$/", "", $entity
    );
  }

  public function initMigrations(
  ): void {
    $this->createEntityPersisteds();
    $this->createEntityStructure();
    $this->createEntityStringSQL();
    $this->createEntityIndexeds();
    $this->createEntityUniques();
    $this->createEntityForeigns();
    $this->updateEntityStructure();
    $this->insertEntityStructure();
    $this->dropsEntityStructure();

    $this->createScripts();
  }

  public function createEntityPersisteds(
  ): void {
    $entityPersisteds = DB::query(
      commandSql: sprintf(
        "select information_schema.columns.table_name as entity
               ,information_schema.columns.column_name as name
               ,information_schema.columns.column_type as type
               ,if(information_schema.columns.is_nullable = 'NO', 'sim', 'não' ) as required
               ,if(information_schema.columns.extra = 'auto_increment', 'sim', 'não' ) as autoinc
			     from information_schema.columns
			    where information_schema.columns.table_schema = '%s'
		   order by information_schema.columns.table_name asc
		           ,information_schema.columns.ordinal_position asc", Application::$database["name"]
      )
    );

    Utils::Map( $entityPersisteds->rows(), function(array $persisted){
      [ "required" => $required,
        "autoinc" => $autoinc,
        "entity" => $entity,
        "name" => $name,
        "type" => $type,
      ] = $persisted;

      $autoincArr = $autoinc === "sim"
        ? [ "autoinc" => "sim" ]
        : [];

      $requiredArr = $required === "sim"
        ? [ "required" => "sim" ]
        : [];

      $this->entitysPersisteds[$entity][$name] = array_merge(
        [ "type" => $type ], $autoincArr, $requiredArr,
      );
    });
  }

  public function createEntityStructure(
  ): void {
    Utils::Map( $this->entitys, 
      fn(string $entity) => (
        $this->loadEntity(
          $entity
        )
      )
    );
  }

  public function loadEntity(
    string $entity
  ): void {
    $this->entitysArr[
      $this->getEntityName($entity)
    ] = $this->defineOrderProperties(
      $this->laodEntityProperties($entity)
    );
  }

  public function laodEntityProperties(
    string $entity,
     array $entityProperies = []
  ): array {
    $entityProperies = Utils::MapKey( array_flip( Utils::Map(
      Reflect::getPropertiesFromReflectClass(
        $entity
      ), fn(ReflectionProperty $property) => (
        $property->getName()
      )
    )), fn($_, $property) => Utils::Map(
      Reflect::getAttronutesFromPropertys($entity, $property), 
      fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance()->get()  
    ));

    return Utils::Map($entityProperies, function($property){
      foreach($property as $attribute){
        foreach($attribute as $key => $value){
          $propertyArr[$key] = $value;
        }        
      }
      return $propertyArr;
    });
  }

  public function defineOrderProperties(
    array $entityProperies = []
  ): array {
    return array_merge(
      array_filter(
        $entityProperies, fn($key) => in_array($key, [
          "Id" 
        ]) === true, ARRAY_FILTER_USE_KEY
      ),
      array_filter(
        $entityProperies, fn($key) => in_array($key, [
          "Id", "Actived", "ActivedBy", "ActivedAt", "CreatedBy", "CreatedAt", "UpdatedBy", "UpdatedAt", "DeletedBy", "DeletedAt"
        ]) === false , ARRAY_FILTER_USE_KEY
      ),
      array_filter(
        $entityProperies, fn($key) => in_array($key, [
          "Actived", "ActivedBy", "ActivedAt", "CreatedBy", "CreatedAt", "UpdatedBy", "UpdatedAt", "DeletedBy", "DeletedAt"
        ]) === true , ARRAY_FILTER_USE_KEY
      )
    );
  }

  public function hasRequired(
    array $attributes
  ): string {
    if (isset($attributes["required"])) {
      return "not null";
    } else if(isset($attributes["autoinc"])) {
      return "not null";
    } else return "null";
  }

  public function hasAutoInc(
    array $attributes
  ): string {
    return isset($attributes["autoinc"]) 
      ? "primary key auto_increment"
      : "";
  }

  public function afterAddColumn(
    string $entity,
    string $property
  ): string {
    $propertysArr = array_keys(
      $this->entitysArr[$entity]
    );

    return $propertysArr[
      array_search($property, $propertysArr) - 1
    ];
  }

  public function createEntityStringSQL(
  ): void {
    Utils::MapKey($this->entitysArr, function(array $properties, string $entity){
      $this->entitysCreateds[$entity] = Utils::MapKey($properties, function(array $attributes, string $property){
        if(isset($attributes["type"])){
          [ "type" => $type ] = $attributes;
          return trim( "`{$property}` {$type} {$this->hasRequired($attributes)} {$this->hasAutoInc($attributes)}" );
        } else return [];
      }); 
    });
  }

  public function createEntityConstrants(
    string $constrantType,
     array $constrantArr = []
  ): void {
    Utils::MapKey($this->entitysArr, function(array $properties, string $entity) use($constrantType) {
      Utils::MapKey($properties, function(array $attributes, string $property) use($entity, $constrantType) {
        if (isset($attributes[$constrantType])) {
          if ($constrantType === "Index") {
            $this->entitysCreatedsIndexes[$entity][
              $attributes[$constrantType]
            ][] = $property;
          } else 
          if ($constrantType === "Unique") {
            $this->entitysCreatedsUniques[$entity][
              $attributes[$constrantType]
            ][] = $property;  
          }          
        }
      });
    });

    if ($constrantType === "Index") {
      $this->entitysCreatedsIndexes = Utils::Map(
        $this->entitysCreatedsIndexes, fn(array $constrantsArr) => (
          Utils::MapKey($constrantsArr, fn(array $constrantList, string $constractOrd) => (
            [ sprintf( "Idx_%s_%s", $constractOrd, implode("_", $constrantList)), 
              implode(",", Utils::Map($constrantList, fn($name) => "`{$name}`")) ]
          ))
        )
      );      
    } else
    if ($constrantType === "Unique") {
      $this->entitysCreatedsUniques = Utils::Map(
        $this->entitysCreatedsUniques, fn(array $constrantsArr) => (
          Utils::MapKey($constrantsArr, fn(array $constrantList, string $constractOrd) => (
            [ sprintf( "Unq_%s_%s", $constractOrd, implode("_", $constrantList)),
              implode(",", Utils::Map($constrantList, fn($name) => "`{$name}`")) ]
          ))
        )
      );
    }
  }

  public function createEntityIndexeds(
  ): void {
    $this->createEntityConstrants("Index");
  }

  public function createEntityUniques(
  ): void {
    $this->createEntityConstrants("Unique");
  }

  public function createEntityForeigns(    
  ): void {
    Utils::MapKey( $this->entitysArr, fn( array $properties, string $entity ) => (
      Utils::MapKey( $properties, function( array $attributes, string $property ) use($entity) {
        if ( isset($attributes["Foreign"]) ){
          $this->entitysCreatedsForeigns[$entity][] = array_merge(
            $attributes["Foreign"], [ 
              "EntityKey" => $property,
              "ReferenceEntity" => $this->getEntityName(
                $attributes["Foreign"]["ReferenceEntity"]
              )
            ]
          ); 
        }
      })
    ));
  }

  public function updateEntityStructure(
  ): void {
    if (sizeof($this->entitysPersisteds) === 0) {
      return ;
    }

    Utils::MapKey( $this->entitysArr, fn( array $properties, string $entity) => (
      Utils::MapKey( $properties, function( array $attributes, string $property) use( $entity ) {
        $hasChangeAttributes = array_filter( $attributes, fn($type) => in_array(
          $type, ["type", "autoinc", "required"]
        ), ARRAY_FILTER_USE_KEY );
  
        if (isset($this->entitysPersisteds[$entity][$property]) && $property !== "Id" ) {
          if ($hasChangeAttributes !== $this->entitysPersisteds[$entity][$property]) {
            $this->entitysUpdates[$entity][$property] = $hasChangeAttributes;
          }
        }
      })
    ));
  }

  public function insertEntityStructure(
  ): void {
    if (sizeof($this->entitysPersisteds) === 0) {
      return ;
    }

    Utils::MapKey( $this->entitysArr, fn( array $properties, string $entity) => (
      Utils::MapKey( $properties, function( array $attributes, string $property) use( $entity ) {
        if ( isset($this->entitysPersisteds[$entity][$property]) === false ) {
          $this->entitysInserts[$entity][$property] = $attributes;
        }
      })
    ));
  }

  public function dropsEntityStructure(
  ): void {
    if (sizeof($this->entitysPersisteds) === 0) {
      return ;
    }
    
    Utils::MapKey( $this->entitysPersisteds, fn( array $properties, string $entity) => (
      Utils::MapKey( $properties, function( array $attributes, string $property) use( $entity ) {
        if ( isset($this->entitysArr[$entity][$property]) === false ) {
          $this->entitysDrops[$entity][$property] = $attributes;
        }
      })
    ));
  }

  public function createScriptTable(
  ): void {
    Utils::MapKey($this->entitysCreateds, function(array $properties, string $entity){
      if (isset($this->entitysPersisteds[$entity]) === false) {
        $this->scriptArr[] = sprintf(
          "create table if not exists `{$entity}` (%s)", implode(",", $properties)
        );
      }
    });
  }

  public function createScriptIndexes(
  ): void {
    Utils::MapKey( $this->entitysCreatedsIndexes, fn(
      array $constrantList, string $entity 
    ) => Utils::Map( $constrantList, fn(array $constrant) => (
      $this->scriptArr[] = "alter table `{$entity}` add index {$constrant[0]} ($constrant[1])"
    )));
  }

  public function createScriptUniques(
  ): void {
    Utils::MapKey($this->entitysCreatedsUniques, fn(
      array $constrantList, string $entity 
    ) => Utils::Map( $constrantList, fn(array $constrant) => (
      $this->scriptArr[] = "alter table `{$entity}` add unique {$constrant[0]} ($constrant[1])"
    )));
  }
  
  public function createScriptForeigns(
  ): void {
    Utils::MapKey( $this->entitysCreatedsForeigns, fn(
      array $foreignsList, string $entity
    ) => Utils::Map( $foreignsList, fn( array $foreigns ) => (
      $this->scriptArr[] = (
        sprintf("alter table `{$entity}` add constraint FK_%s_in_{$entity} foreign key (%s) references %s(%s)",
          $foreigns["ReferenceEntity"],
          $foreigns["EntityKey"],
          $foreigns["ReferenceEntity"],
          $foreigns["ReferenceKey"]
        )
      )
    )));
  }

  public function updateScriptCollumns(
  ): void {
    Utils::MapKey( $this->entitysUpdates, fn(array $properties, string $entity) => (
      Utils::MapKey( $properties, function(array $attributes, string $property) use($entity) {
        [ "type" => $type ] = $attributes;

        $this->scriptArr[] = sprintf(
          "alter table `{$entity}` modify column `{$property}` {$type} %s", $this->hasRequired($attributes)
        );
      })
    ));
  }

  public function insertScriptCollumns(
  ): void {
    Utils::MapKey( $this->entitysInserts, fn(array $properties, string $entity) => (
      Utils::MapKey( $properties, function(array $attributes, string $property) use($entity) {
        [ "type" => $type ] = $attributes;

        $this->scriptArr[] = sprintf(
          "alter table `{$entity}` add column `{$property}` {$type} %s after %s", $this->hasRequired($attributes), $this->afterAddColumn($entity, $property)
        );
      })
    ));
  } 
  
  public function dropsScriptCollumns(
  ): void {
    Utils::MapKey( $this->entitysDrops, fn(array $properties, string $entity) => (
      Utils::MapKey( $properties, function(array $_, string $property) use($entity) {
        $this->scriptArr[] = "alter table `{$entity}` drop column `{$property}`";
      })
    ));    
  }

  public function createScriptExecute(
  ): void {
    DB::query(
      commandSql: $this->scriptArr
    );
  }

  public function createScripts(
  ): void {
    $this->createScriptTable();
    $this->createScriptIndexes();
    $this->createScriptUniques();
    $this->createScriptForeigns();     
    $this->updateScriptCollumns();
    $this->insertScriptCollumns();
    $this->dropsScriptCollumns();
    $this->createScriptExecute();
  }

  public static function create(
    array $entitys = []
  ): Migrations {
    return new static(
      entitys: $entitys
    );
  }
}