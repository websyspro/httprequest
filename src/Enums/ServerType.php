<?php

namespace Websyspro\Core\Enums;

class ServerType
{
  public const REQUEST_URI_EMPTY = "";
  public const PATH_INFO = "PATH_INFO";
  public const REQUEST_METHOD = "REQUEST_METHOD";
  public const REQUEST_PHP_INPUT = "php://input";
  public const REQUEST_AUTHENTICATE = "authenticate";
  public const REQUEST_URI = "REQUEST_URI";
  public const PUBLIC_INIT = "Public/index.php";
  public const SERVER_RUNNING = "server running";
}