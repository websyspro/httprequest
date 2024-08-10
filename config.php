<?php

if (defined("DIRECTORY_SEPARATOR_WINDOWS") === false) {
  define("DIRECTORY_SEPARATOR_WINDOWS", "\\");
}

if (defined("DIRECTORY_SEPARATOR_LINUX") === false) {
  define("DIRECTORY_SEPARATOR_LINUX", "/");
}

if (defined("DIRECTORY_CURRENT") === false) {
  define("DIRECTORY_CURRENT", __DIR__ . DIRECTORY_SEPARATOR_LINUX );
}

if (defined("API_BASE") === false) {
  define( "API_BASE", "api/v1" );
}

if (defined("API_PORT") === false) {
  define( "API_PORT", "8080" );
}