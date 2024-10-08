<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionAttribute;
use ReflectionProperty;

use Websyspro\HttpRequest\Common\Utils;
use Websyspro\HttpRequest\Server\Drivers\DB;

class Migrations
{
  private array $logsErr = [];
  public array $entitysArr = [];
  public array $entitysPersisteds = [];
  public array $entitysCreateds = [];
  public array $entitysUpdates = [];
  public array $entitysInserts = [];
  public array $entitysDrops = [];
  public array $entitysCreatedsIndexes = [];
  
  private array $persistedIndexes = [];
  private array $persistedUniques = [];
  private array $persistedForeigns = [];
  private array $persistedDropsForeigns = [];
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
    if (isset($_SERVER[ "argv" ]) === false) {
      return false;
    } else {

      [ "argv" => $argv,
        "argc" => $argc ] = $_SERVER;

      if ( (int)$argc >= 2 ) {
        [ , $cmd ] = $argv;

        if (empty($cmd) === false) {
          if ( $cmd === "migrate" ) {
            return true;
          } else return false;
        } else return false;
      } else return false;
    }
  }

  public function getEntityName(
    string $entity
  ): string {
    $entityArr = explode("\\", $entity);
    return preg_replace(
      "/Entity$/", "", end($entityArr)
    );
  }

  public function initMigrations(
  ): void {
    $this->ObterPersistedsEntitys();
    $this->ObterPersistedsIndexes();
    $this->ObterPersistedsUniques();
    $this->ObterPersistedsForeigns();
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

  private function ObterDatabase(): string {
    return Application::$database["name"];
  }

  private function ObterPersistedsEntitysInDatabase(
  ): string {
    return Utils::Str(
      "select information_schema.columns.table_name as entity
              ,information_schema.columns.column_name as name
              ,information_schema.columns.column_type as type
              ,if(information_schema.columns.is_nullable = 'NO', 'sim', 'não' ) as required
              ,if(information_schema.columns.extra = 'auto_increment', 'sim', 'não' ) as autoinc
			    from information_schema.columns
			   where information_schema.columns.table_schema = '{$this->ObterDatabase()}'
		  order by information_schema.columns.table_name asc
		          ,information_schema.columns.ordinal_position asc"
    );
  }

  private function DefinePersistedsEntitys(
    array $EntitysArr = []
  ): void {
    [ $entity, $name, $type, $required, $autoinc ] = array_values($EntitysArr);
    
    $this->entitysPersisteds[$entity][$name] = array_merge(
      [ "type" => $type ], $autoinc === "sim" ? [ "autoinc" => "sim" ] : [], $required === "sim" ? [ "required" => "sim" ] : [],
    );    
  }

  public function ObterPersistedsEntitys(
  ): void {
    Utils::Map( DB::query(
      commandSql: $this->ObterPersistedsEntitysInDatabase()
    )->ObterRows(), fn( array $row ) => $this->DefinePersistedsEntitys( $row ));
  }

  private function ObterPersistedsStatisticsInDatabase(
    string $statisticsType    
  ): string {
    return Utils::Str(
      "select information_schema.statistics.table_name as entity
			       ,information_schema.statistics.index_name as statistics
		     from information_schema.statistics 
		    where information_schema.statistics.table_schema = '{$this->ObterDatabase()}'
		      and information_schema.statistics.index_name like '{$statisticsType}_%'"
    );
  }

  private function DefinePersistedsStatistics(
    array $StatisticsArr = []
  ): void {
    [ "entity" => $entity, "statistics" => $statistics ] = $StatisticsArr;

    if ( preg_match("/^Idx/", $statistics )){
      $this->persistedIndexes[$entity][] = $statistics;
    } else $this->persistedUniques[$entity][] = $statistics;
  }

  private function ObterPersistedsIndexes(
  ): void {
    Utils::Map( DB::query(
      commandSql: $this->ObterPersistedsStatisticsInDatabase("Idx")
    )->ObterRows(), fn( array $row ) => $this->DefinePersistedsStatistics( $row ));
  } 

  private function ObterPersistedsUniques(
  ): void {
    Utils::Map( DB::query(
      commandSql: $this->ObterPersistedsStatisticsInDatabase("Unq")
    )->ObterRows(), fn( array $row ) => $this->DefinePersistedsStatistics( $row ));
  }

  private function ObterPersistedsForeignsInDatabase(
  ): string {
    return Utils::Str(
      "select information_schema.referential_constraints.constraint_name as constranit
  		   from information_schema.referential_constraints 
  		  where constraint_schema = '{$this->ObterDatabase()}'"
    );
  }

  private function ObterPersistedsForeigns(
  ): void {
    Utils::Map( DB::query(
      commandSql: $this->ObterPersistedsForeignsInDatabase()
    )->ObterRows(), fn(array $row) => $this->persistedForeigns[] = $row[ "constranit" ]);
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

  private function loadEntity(
    string $entity
  ): void {
    $this->entitysArr[
      $this->getEntityName($entity)
    ] = Migrations::defineOrderProperties(
      Migrations::ObterEntityProperties($entity)
    );
  }

  public static function ObterEntityStructure(
    string $Entity
  ): array {
    return Migrations::defineOrderProperties(
      Migrations::ObterEntityProperties($Entity)
    );
  }

  public static function ObterEntityProperties(
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

  public static function defineOrderProperties(
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
          "Id", "Actived", "ActivedBy", "ActivedAt", "CreatedBy", "CreatedAt", "UpdatedBy", "UpdatedAt", "Deleted", "DeletedBy", "DeletedAt"
        ]) === false , ARRAY_FILTER_USE_KEY
      ),
      array_filter(
        $entityProperies, fn($key) => in_array($key, [
          "Actived", "ActivedBy", "ActivedAt", "CreatedBy", "CreatedAt", "UpdatedBy", "UpdatedAt", "Deleted", "DeletedBy", "DeletedAt"
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
  
        if ( isset($this->entitysPersisteds[$entity])) {
          if (isset($this->entitysPersisteds[$entity][$property]) && $property !== "Id" ) {
            if ($hasChangeAttributes !== $this->entitysPersisteds[$entity][$property]) {
              $this->entitysUpdates[$entity][$property] = $hasChangeAttributes;
            }
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
        if ( isset($this->entitysPersisteds[$entity])) {
          if ( isset($this->entitysPersisteds[$entity][$property]) === false ) {
            $this->entitysInserts[$entity][$property] = $attributes;
          }
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

  public function ExecuteScriptTable(
  ): void {
    Utils::MapKey($this->entitysCreateds, function(array $properties, string $entity){
      if (isset($this->entitysPersisteds[$entity]) === false) {
        $this->scriptArr[] = sprintf(
          "create table if not exists `{$entity}` (%s)", Utils::Join($properties)
        );
      }
    });
  }

  private function hasPersistedsIndex(
    string $entity,
    string $constraint,
  ): bool {
    if (isset($this->persistedIndexes[$entity])) {
      return in_array($constraint, $this->persistedIndexes[$entity]);
    } else return false;
  }

  private function ObterScriptAddIndex(
    string $entity,
    array $constraintArr = []
  ): void {
    [ $constraint, $properties ] = $constraintArr;
    if ($this->hasPersistedsIndex( $entity, $constraint ) === false) {
      $this->scriptArr[] = "alter table `{$entity}` add index {$constraint} ($properties)";
    }
  }

  public function ExecuteScriptIndexes(
  ): void {
    Utils::MapKey( $this->entitysCreatedsIndexes, fn(array $constraints, string $entity) => (
      Utils::Map( $constraints, fn(array $constraint) => $this->ObterScriptAddIndex($entity, $constraint))
    ));

    if (sizeof($this->entitysCreatedsIndexes) !== 0){
      Utils::MapKey( $this->entitysCreatedsIndexes, fn(array $constraints, string $entity) => (
        Utils::Map( array_diff(isset($this->persistedIndexes[$entity]) === true ? $this->persistedIndexes[$entity] : [], array_values( 
          Utils::Map( $constraints, fn(array $constraint) => Utils::ArrayFirtsValue($constraint))
        )), fn(string $contraint) => $this->scriptArr[] = "alter table `{$entity}` drop index `{$contraint}`" )
      ));
    } else {
      Utils::MapKey($this->persistedIndexes, fn(array $constraints, string $entity) => (
        Utils::Map($constraints, fn(string $contraint) => (
          $this->scriptArr[] = "alter table `{$entity}` drop index `{$contraint}`"
        ))
      ));
    }
  }

  private function hasPersistedsUnique(
    string $entity,
    string $constraint,
  ): bool {
    if (isset($this->persistedUniques[$entity])) {
      return in_array($constraint, $this->persistedUniques[$entity]);
    } else return false;
  }  

  private function ObterScriptAddUnique(
    string $entity,
    array $constraintArr = []
  ): void {
    [ $constraint, $properties ] = $constraintArr;
    if ($this->hasPersistedsUnique( $entity, $constraint) === false) {
      $this->scriptArr[] = "alter table `{$entity}` add unique {$constraint} ($properties)";
    }
  }

  public function ExecuteScriptUniques(
  ): void {
    Utils::MapKey($this->entitysCreatedsUniques, fn( array $constrantList, string $entity) => (
      Utils::Map( $constrantList, fn(array $constraint) => $this->ObterScriptAddUnique( $entity, $constraint ))
    ));

    if (sizeof($this->entitysCreatedsUniques) !== 0){
      Utils::MapKey( $this->entitysCreatedsUniques, function(array $constraints, string $entity){
        Utils::Map( array_diff( isset($this->persistedUniques[$entity]) === true ? $this->persistedUniques[$entity] : [], array_values( 
          Utils::Map( $constraints, fn(array $constraint) => Utils::ArrayFirtsValue($constraint))
        )), fn(string $contraint) => $this->scriptArr[] = "alter table `{$entity}` drop index `{$contraint}`" );
      });
    } else {
      Utils::MapKey($this->persistedUniques, fn(array $constraints, string $entity) => (
        Utils::Map($constraints, fn(string $contraint) => (
          $this->scriptArr[] = "alter table `{$entity}` drop index `{$contraint}`"
        ))
      ));
    }
  }

  private function ObterForeignsName(
    string $entity,
     array $constraint = []    
  ): string {
    [ $reference, $referenceKey ] = array_values( $constraint );
    return "Fk_{$reference}_in_{$entity}_to_{$referenceKey}";
  }  

  private function ObterScriptAddForeigns(
    string $entity,
     array $constraint = []
  ): void {
    [ $reference, $referenceKey, $key ] = array_values( $constraint );
    if ( in_array($this->ObterForeignsName($entity, $constraint), $this->persistedForeigns) === false ) {
      $this->scriptArr[] = "alter table `{$entity}` add constraint {$this->ObterForeignsName($entity, $constraint)} foreign key ({$key}) references {$reference}({$referenceKey})";
    }
  }

  private function ObterScriptDropForeigns(
    string $entity,
    string $constraint
  ): void {
    $this->scriptArr[] = "alter table `{$entity}` drop foreign key `{$constraint}`";
    $this->scriptArr[] = "alter table `{$entity}` drop index `{$constraint}`";
  }  

  public function ExecuteScriptForeigns(
  ): void {
    Utils::MapKey( $this->entitysCreatedsForeigns, fn( array $constraintArr, string $entity ) => (
      Utils::Map( $constraintArr, fn( array $constraint ) => (
        $this->ObterScriptAddForeigns( $entity, $constraint )
      ))
    ));

    Utils::MapKey( $this->entitysCreatedsForeigns, 
      fn( array $constraints, string $entity) => (
        Utils::Map( $constraints, fn( array $constraint) => (
          $this->persistedDropsForeigns[$entity] = $this->ObterForeignsName(
            $entity, $constraint
          )
        ))
      )
    );

    if (sizeof($this->entitysCreatedsForeigns) !== 0) {
      Utils::MapKey(
        array_filter(
          $this->persistedDropsForeigns, fn(string $constraint) => (
            in_array( $constraint, $this->persistedForeigns )
          )
        ), function( string $constraint, string $entity) {
          $this->ObterScriptDropForeigns($entity, $constraint);
        }
      );
    } else if ( sizeof($this->persistedForeigns)) {
      Utils::Map( $this->persistedForeigns, function( string $constraint ){
        [ , , , $entity ] = explode(
          "_", $constraint
        );

        $this->ObterScriptDropForeigns($entity, $constraint);
      });
    }
  } 

  public function ExecuteScriptUpdateCols(
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

  public function ExecuteScriptInsertCols(
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
  
  public function ExecuteScriptDropCols(
  ): void {
    Utils::MapKey( $this->entitysDrops, fn(array $properties, string $entity) => (
      Utils::MapKey( $properties, function(array $_, string $property) use($entity) {
        $this->scriptArr[] = "alter table `{$entity}` drop column `{$property}`";
      })
    ));    
  }

  public function ExecuteScriptAll(
  ): void {
    if (sizeof($this->scriptArr)) {
      Utils::Map($this->scriptArr, function(string $script){
        $command = DB::query(
          commandSql: $script
        );

        Utils::Logger( "\x1b[32mExecute SQL: \x1b[37m{$script}." );

        if ($command->hasError()) {
          Utils::Logger( " |-> \x1b[31mError SQL: \x1b[37m{$command->ObterError()}." );
        }
      });
    }
  }

  public function createScripts(
  ): void {
    $this->ExecuteScriptTable();
    $this->ExecuteScriptUpdateCols();
    $this->ExecuteScriptInsertCols();
    $this->ExecuteScriptIndexes();
    $this->ExecuteScriptUniques();
    $this->ExecuteScriptForeigns();
    $this->ExecuteScriptDropCols();
    $this->ExecuteScriptAll();
  }

  public static function create(
    array $entitys = []
  ): Migrations {
    return new static(
      entitys: $entitys
    );
  }
}