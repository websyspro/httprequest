<?php

namespace Websyspro\Core\Enums;

enum ContentType:string {
  case ApplicationJson = "application/json";
  case MultipartFormData = "multipart/form-data";
  case ApplicationXWwwFormUrlencoded = "application/x-www-form-urlencoded";
}