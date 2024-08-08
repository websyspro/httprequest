<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Enums\ContentType;

class Request {
  private string $RequestMethod;
  private string $RequestUri;
  private string $ContentType;
  private int $ContentLength;
  private mixed $body;
  private object $files;

  public function __construct() {
    $this->DefineProperties();
    $this->DefineBodyArgs();
    $this->DefineParamsArgs();
  }

  public static function create(): Request {
    return new static();
  }

  public function DefineProperties(): void {
    [ "REQUEST_METHOD" => $this->RequestMethod,
      "CONTENT_LENGTH" => $this->ContentLength,  
      "CONTENT_TYPE" => $this->ContentType,
      "REQUEST_URI" => $this->RequestUri,
    ] = $_SERVER;

    if (preg_match('/^multipart\/form-data/', $this->ContentType)) {
      [ $this->ContentType ] = explode(
        ";", $this->ContentType
      );
    }
  }

  public function getRequestMethod(): string {
    return $this->RequestMethod;
  }

  public function getRequestUri(): string {
    return $this->RequestUri;
  }

  private function DefineBodyArgs(): void {
    if ($this->ContentLength !== 0) {
      $this->body = match($this->ContentType) {
        ContentType::ApplicationJson->value => Utils::getBodyApplicationJSON(),
        ContentType::MultipartFormData->value => Utils::getBodyMultipartFormData(),
        ContentType::XWwwFormUrlencoded->value => Utils::getBodyFormUrlEncoded()
      };
    }
  }

  private function DefineParamsArgs(): void {
    print_r($this);
  }
}