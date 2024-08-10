<?php

namespace Websyspro\Core\Enums;

enum ContentType:string {
  case TextPlain = "text/plain";
  case ApplicationJson = "application/json";
  case MultipartFormData = "multipart/form-data";
  case XWwwFormUrlencoded = "application/x-www-form-urlencoded";
}