<?php

namespace Websyspro\HttpRequest\Server;

use ReflectionProperty;
use Websyspro\HttpRequest\Common\Utils;

class Migrations
{
  public array $modelsArr = [];

  public function __construct(
    private array $models = []
  ){
    $this->init();
  }

  public function init(
  ): void {
    Utils::Map( $this->models, 
      fn(string $model) => (
        $this->loadModel(
          $model
        )
      )
    );
  }

  public function loadModel(
    string $model
  ): mixed {
    $modelsArr[$model][] = Utils::Map(Reflect::getPropertiesFromReflectClass($model), 
      fn(ReflectionProperty $reflectionProperty) => $reflectionProperty->name
    );

    Utils::Map($modelsArr[$model], function($propertyArr) use($model){
      Utils::Map($propertyArr, function($property) use($model) {
        $attributes = (new ReflectionProperty($model, $property))->getAttributes();
        print_r($attributes[0]->newInstance()->get());
      });
    });


    return $model;
  }

  public static function create(
    array $models = []
  ): Migrations {
    return new static(
      models: $models
    );
  }
}