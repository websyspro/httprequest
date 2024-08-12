<?php

namespace Websyspro\HttpRequest\Enums;

class MultipartFormDataAttrs {
  public const ContentDisposition = "ContentDisposition";
  public const FormDataReadType = "r";
  public const FormDataTypeFile = "FILE";
  public const FormDataTypeField = "FIELD";
  public const ContentType = "ContentType";
  public const ContentBody = "ContentBody";
  public const FormDataEndBody = "\r\n";
  public const FormDataDefaultFile = "php://input";
  public const FormDataContentDisposition = "/^Content-Disposition/i";
  public const FormDataContentType = "/^Content-Type/i";
  public const FormDataFileName = "name";
  public const FormDataFileSize = "size";
  public const FormDataFileType = "type";
  public const FormDataFileBody = "file";
  public const ApplicationOctetStream = "application/octet-stream";
  public const ApplicationFormData = "application/form-data";
  public const FormDataRegExpStartFormData = "/(-){28}[0-9]{24}(-){0}$/i";
  public const FormDataRegExpEndFormData = "/(-){28}[0-9]{24}(-){2}$/i";
}