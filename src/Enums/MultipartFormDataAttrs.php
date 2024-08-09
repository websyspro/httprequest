<?php

namespace Websyspro\Core\Enums;

enum MultipartFormDataAttrs:string {
  case ContentDisposition = "ContentDisposition";
  case FormDataReadType = "r";
  case FormDataTypeFile = "FILE";
  case FormDataTypeField = "FIELD";
  case ContentType = "ContentType";
  case ContentBody = "ContentBody";
  case FormDataEndBody = "\r\n";
  case FormDataDefaultFile = "php://input";
  case FormDataContentDisposition = "/^Content-Disposition/i";
  case FormDataContentType = "/^Content-Type/i";
  case FormDataFileName = "name";
  case FormDataFileSize = "size";
  case FormDataFileType = "type";
  case FormDataFileBody = "file";
  case ApplicationOctetStream = "application/octet-stream";
  case ApplicationFormData = "application/form-data";
  case FormDataRegExpStartFormData = "/(-){28}[0-9]{24}(-){0}$/i";
  case FormDataRegExpEndFormData = "/(-){28}[0-9]{24}(-){2}$/i";
}