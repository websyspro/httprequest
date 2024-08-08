<?php

namespace Websyspro\Core\Enums;

enum RequestMethod {
  case POST;
  case GET;
  case PUT;
  case PATCH;
  case DELETE;
  case HEAD;
  case OPTIONS;
}