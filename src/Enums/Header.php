<?php

namespace Websyspro\HttpRequest\Enums;

class Header
{
  public const ContentType = "CONTENT_TYPE";
  public const ApplicationJSON = "Content-Type: application/json; charset=utf-8";
  public const AccessControlAllowOrigin = "Access-Control-Allow-Origin: *";
  public const AccessControlAllowHeaders = "Access-Control-Allow-Headers: *";
  public const AccessControlAllowMethods = "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS";

}