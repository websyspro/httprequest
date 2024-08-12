<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\MultipartFormDataAttrs;

class FileDataList
{
  public array $dataList;

  public function __construct(
    array $filesArr = []
  ){
    $this->LoadFiles(
      $filesArr
    );
  }

  public function LoadFiles(array $filesArr = []): void
  {
    if (sizeof($filesArr) !== 0)
    {
      foreach ($filesArr as $key => $fileAttr)
      {
        foreach(array_keys($fileAttr) as $fileAttrKey)
        {
          $this->dataList[$key][
            match($fileAttrKey){
              MultipartFormDataAttrs::FormDataFileName => "name",
              MultipartFormDataAttrs::FormDataFileSize => "size",
              MultipartFormDataAttrs::FormDataFileType => "type",
              MultipartFormDataAttrs::FormDataFileBody => "body"
            }
          ] = $fileAttr[$fileAttrKey];
        }
      }
  
      $this->dataList = Utils::Map(
        $this->dataList, fn($file) => (object)$file
      );
    }
  }

  public function getFile( string $file ): array
  {
    return $this->dataList[$file];
  }

  public function getFiles(): array
  {
    return $this->dataList;
  }  

  public static function create(array $filesArr = []): FileDataList
  {
    return new static(
      $filesArr
    );
  }
}