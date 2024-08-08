<?php

namespace Websyspro\Core\Server;
use Websyspro\Core\Enums\RequestMethod;

class Utils {

  public static function getRequestMethod(): string {
    [ "REQUEST_METHOD" => $RequestMethod ] = $_SERVER;
    return $RequestMethod;
  }

  public static function getPostFile(): array {
    print_r(explode(PHP_EOL, static::getFileContents()));
    return [];
  }

  public static function getPost(): array {
    return $_POST;
  }

  public static function getFileContents(): mixed {
    return file_get_contents( "php://input", true );
  }

  public static function getBodyApplicationJSON(): mixed {
    return json_decode( static::getFileContents());
  }

  public static function getBodyFormUrlEncoded(): mixed {
    parse_str( static::getFileContents(), $urlEncoded);
    return $urlEncoded;
  }

  public static function getBodyMultipartFormData(): mixed {
    return static::getRequestMethod() !== RequestMethod::POST->name
      ? static::getPostFile()
      : static::getPost();
  }
}