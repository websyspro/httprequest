<?php

namespace Websyspro\Core\Server;

use ReflectionAttribute;
use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\ConstructStructure;
use Websyspro\Core\Enums\Decoration;
use Websyspro\Core\Enums\Method;
use Websyspro\Core\Enums\MethodStructure;
use Websyspro\Core\Enums\MiddlewareStructure;

class ServerUtils
{
  public static function getControllerName(
    string $controller
  ): string {
    return sprintf(
      "%s{$controller}", DIRECTORY_SEPARATOR_WINDOWS
    );
  }

  public static function setDropBarAfter(
    string $path
  ): string {
    return preg_replace(
      "/\/$/", "", $path
    );
  }

  public static function getApiBase(
  ): string {
    return DIRECTORY_SEPARATOR_LINUX . API_BASE;
  }

  public static function GetApiBarSep(
  ): string {
    return DIRECTORY_SEPARATOR_LINUX;
  }
  
  public static function GetController(
    string $controller
  ): string {
    $AttributeFromController = Utils::Filter(Reflect::getAttributesFromReflectClass($controller), 
      fn(ReflectionAttribute $controller) => (
        $controller->getName()::TypeDecoration === Decoration::Controller
      )
    );

    $ApiController = Utils::Map($AttributeFromController, 
      fn($api) => Utils::ArrayFirtsValue($api->getArguments())
    );

    return Utils::ArrayFirtsValue(
      $ApiController
    );
  }  
  
  public static function GetControllerApi(
    string $controller
  ): string {
    return implode(
      ServerUtils::GetApiBarSep(), [
      ServerUtils::GetApiBase(),
      ServerUtils::GetController(
        $controller
      )
    ]);    
  }
  
  public static function GetConstruct(
    string $controller
  ): array {
    $constrctFromController = Utils::Filter(get_class_methods($controller),
      fn($method) => $method === Method::Contruct
    );

    $parametersFromContruct = Utils::Map($constrctFromController,
      fn($method) => Reflect::getReflectMethod(
        $controller, $method
      )->getParameters()
    );

    $parametersFromContruct = Utils::Map($parametersFromContruct,
      fn($parameters) => Utils::Map($parameters, 
        fn($parameter) => DIRECTORY_SEPARATOR_WINDOWS . $parameter->getType()->getName()
      )
    );

    return [
      ConstructStructure::MethodName => Method::Contruct,
      ConstructStructure::MethodParameters => Utils::ArrayFirtsValue(
        $parametersFromContruct
      )
    ];
  }
  
  public static function GetMiddlewares(
    string $controller
  ): array {
    $MiddlewareFromController = Utils::Filter(Reflect::getAttributesFromReflectClass($controller),
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Middleware
      )
    );

    return Utils::Map($MiddlewareFromController, fn($attribute) => [
      MiddlewareStructure::InstanceClass => DIRECTORY_SEPARATOR_WINDOWS . $attribute->getName(),
      MiddlewareStructure::ArgsClass => $attribute->getArguments()
    ]);
  }

  public static function GetRoute(
    string $controller,
    string $method
  ): string {
    $routeFromMethod = Utils::Filter(Reflect::getReflectMethod($controller, $method)->getAttributes(),
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Route
      )
    );

    $ApiController = Utils::Map($routeFromMethod, 
      fn($api) => Utils::ArrayFirtsValue($api->getArguments())
    );

    return Utils::ArrayFirtsValue(
      empty( $ApiController[0]) === false 
        ? $ApiController : [ "" ]
    );
  }  

  public static function GetMethodApi(
    string $Controller,
    string $Method    
  ): string {
    return implode(
      static::GetApiBarSep(), [
      static::GetApiBase(), 
      static::GetController($Controller),
      static::GetRoute(
        $Controller,
        $Method
      )
    ]);
  }

  public static function GetMethodName(
    string $Controller,
    string $Method
  ): string {
    return $Controller
      ? $Method
      : $Method;
  }

  public static function GetMethodType(
    string $Controller,
    string $Method
  ): string {
    $AttributesFromMethod = Reflect::getReflectMethod(
      $Controller, $Method
    )->getAttributes();

    $AttributesFromMethod = Utils::Filter($AttributesFromMethod, 
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Route
      )
    );

    $AttributesFromMethod = Utils::Map(
      $AttributesFromMethod,
        fn($Attribute) => (string)$Attribute->getName()::TypeHttp
    );

    return Utils::ArrayFirtsValue(
      $AttributesFromMethod
    );
  }

  public static function GetMethodParameters(
    string $Controller,
    string $Method
  ): array {
    $ParametersFromMethod = Reflect::getParametersFromReflectMethod(
      $Controller, $Method
    );

    $AttributesFromParametersFromMethod = Utils::Map($ParametersFromMethod, 
      fn($parameters) => $parameters->getAttributes()
    );

    $AttributesFromParametersFromMethod = Utils::Map(
      $AttributesFromParametersFromMethod, 
        fn($attributes) => Utils::ArrayFirtsValue(Utils::Map($attributes,
          fn($attribute) => DIRECTORY_SEPARATOR_WINDOWS . $attribute->getName()
        ))
    );

    return $AttributesFromParametersFromMethod;
  }
  
  public static function GetMethodMiddleware(
    string $controller,
    string $method
  ): array {
    $AttributesFromMethod = Reflect::getAttributesFromReflectMethod(
      $controller, $method
    );

    $MiddlewareFromMethod = Utils::Filter($AttributesFromMethod,
      fn(ReflectionAttribute $Attribute) => (
        $Attribute->getName()::TypeDecoration === Decoration::Middleware
      )
    );

    return Utils::Map($MiddlewareFromMethod,
      fn($attribute) => [
        MiddlewareStructure::InstanceClass => $attribute->getName(),
        MiddlewareStructure::ArgsClass => $attribute->getArguments()
      ]
    );
  } 

  public static function GetMethods(
    string $Controller
  ): array {
    $MethodsFromController = Utils::Filter(get_class_methods($Controller),
      fn($Method) => $Method !== Method::Contruct
    );

    $MethodsFromController = Utils::Map($MethodsFromController,
      fn($Method) => [
        MethodStructure::MethodApi => static::GetMethodApi($Controller, $Method),
        MethodStructure::MethodUri => static::GetRoute($Controller, $Method),
        MethodStructure::MethodName => static::GetMethodName($Controller, $Method),
        MethodStructure::MethodType => static::GetMethodType($Controller, $Method),
        MethodStructure::MethodParameters => static::GetMethodParameters($Controller, $Method),
        MethodStructure::MethodMiddleware => static::GetMethodMiddleware($Controller, $Method),
      ]
    );

    return $MethodsFromController;
  }  
}