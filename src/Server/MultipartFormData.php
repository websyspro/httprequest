<?php

namespace Websyspro\Core\Server;

use Websyspro\Core\Common\Utils;
use Websyspro\Core\Enums\MultipartFormDataAttrs;

class MultipartFormData
{
  private mixed $formDataStream;
  private mixed $lineBuffer;
  private array $formDataList = [];
  private int $cursor = -1;
  private array $formDataContentBody;
  private string $formDataContentType = "";
  private string $formDataContentDisposition = "";

  public function __construct(
    private string $formDataFile = ""
  ){
    empty($this->formDataFile)
      ? $this->ReadPost() 
      : $this->ReadFile();
  }

  public static function LoadPHPInput(): MultipartFormData
  {
    return new static(
      MultipartFormDataAttrs::FormDataDefaultFile->value
    );
  }

  public static function LoadPost(): MultipartFormData
  {
    return new static();
  }

  private function GetFormDataStream(
  ): mixed {
    return $this->formDataStream;
  }

  private function GetLineSize(
  ): int {
    return 4096;
  }

  private function ReadFileEof(
  ): bool {
    return preg_match_all(
      MultipartFormDataAttrs::FormDataRegExpEndFormData->value,
      rtrim($this->lineBuffer)
    );
  }

  private function ReadFileFormData(
  ): bool {
    return $this->ReadFileBof() === false
        && $this->ReadFileEof() === false;
  }

  private function ReadFileContentDisposition(
  ): bool {
    return preg_match_all(
      MultipartFormDataAttrs::FormDataContentDisposition->value, 
      rtrim(
        $this->lineBuffer
      )
    );    
  }

  private function AddReadFileContentDisposition(
  ): void {
    $this->formDataContentDisposition = trim(
      $this->lineBuffer
    );
  }  

  private function GetReadFileContentDisposition(
  ): string {
    return $this->formDataContentDisposition;
  }   

  private function ReadFileContentType(
  ): bool {
    return preg_match_all(
      MultipartFormDataAttrs::FormDataContentType->value, 
      rtrim(
        $this->lineBuffer
      )
    );
  }

  private function AddReadFileContentType(
  ): void {
    $this->formDataContentType = MultipartFormDataAttrs::ApplicationOctetStream->value;
  }

  private function GetFormDataContentType(
  ): string {
    return $this->formDataContentType;
  }  

  private function ReadFileBody(
  ): bool {
    return $this->ReadFileContentDisposition() === false
        && $this->ReadFileContentType() === false;
  }

  private function AddFormDataContentBody(
  ): void {
    if ($this->GetFormDataContentType() === MultipartFormDataAttrs::ApplicationFormData->value){
      $this->formDataContentBody[] = str_replace(
        MultipartFormDataAttrs::FormDataEndBody->value, "", $this->lineBuffer
      );
    } else {
      $this->formDataContentBody[] = $this->lineBuffer;
    }
  }

  private function GetFormDataContentBody(
  ): array {
    return array_slice(
      $this->formDataContentBody, 1
    );
  }  

  private function ReadFileIncrementCursor(
  ): void {
    $this->cursor++;
  }

  private function ReadFileContentsClear(
  ): void {
    $this->formDataContentBody = [];
    $this->formDataContentType = MultipartFormDataAttrs::ApplicationFormData->value;
  }

  private function ReadFileBof(
  ): bool {
    return preg_match_all(
      MultipartFormDataAttrs::FormDataRegExpStartFormData->value,
      rtrim($this->lineBuffer)
    );
  }

  private function ReadFileStream(
  ): void {
    $this->formDataStream = fopen(
      $this->formDataFile, MultipartFormDataAttrs::FormDataReadType->value
    );
  }

  private function ReadFileLoad(
  ): void {
    while ((
      $this->lineBuffer = fgets(
        $this->GetFormDataStream(),
        $this->GetLineSize()
      )
    ) !== false) {
      if ($this->ReadFileBof()) {
        $this->ReadFileIncrementCursor();
        $this->ReadFileContentsClear();
      }

      if ($this->ReadFileFormData()) {
        if ($this->ReadFileContentDisposition()) {
          $this->AddReadFileContentDisposition();
        }

        if ($this->ReadFileContentType()) {
          $this->AddReadFileContentType();
        }
        
        if ($this->ReadFileBody()) {
          $this->AddFormDataContentBody();
        }

        $this->formDataList[$this->cursor] = [
          MultipartFormDataAttrs::ContentDisposition->name => $this->GetReadFileContentDisposition(),
          MultipartFormDataAttrs::ContentType->name => $this->GetFormDataContentType(),
          MultipartFormDataAttrs::ContentBody->name => $this->GetFormDataContentBody()
        ]; 
      }
    }    
  }

  private function CloseFileStream(
  ): void {
    fclose($this->formDataStream);
  }  

  private function ReadFileStreamJoin(
  ): void {
    $this->formDataList = Utils::Map(
      $this->formDataList, function($formData){
        if ($formData === MultipartFormDataAttrs::ApplicationFormData->value) {
          return $formData;
        }

        return array_merge(
          $formData, [
            MultipartFormDataAttrs::ContentBody->name => implode(
              "", $formData[MultipartFormDataAttrs::ContentBody->name]
            )
          ]
        );
      }
    );
  }

  private function GetFormDataKey(
    array $formData
  ): string {
    list( $fieldname ) = array_slice(
      explode(";", $formData[MultipartFormDataAttrs::ContentDisposition->name]),
    1);

    list( $fieldname ) = array_slice(
      explode("=", $fieldname), 1
    );

    return str_replace(
      "\"", "", $fieldname
    );
  }

  private function GetFormDataFilename(
    array $formData
  ): string {
    list( $filename ) = array_slice(explode(
      ";", $formData[MultipartFormDataAttrs::ContentDisposition->name]
    ), 2);

    list( $filename ) = array_slice(
      explode("=", $filename), 1
    );    

    return str_replace(
      "\"", "", $filename
    );
  }

  private function GetFileSize(
    string $formDataFile
  ): float {
    return (float)bcdiv(
      strlen($formDataFile), 1000, 3
    );
  }

  private function ReadFileDefinedExtract(
  ): void {
    $this->formDataList = Utils::Map(
      $this->formDataList, function($formData){
        if ($formData[MultipartFormDataAttrs::ContentType->name] === MultipartFormDataAttrs::ApplicationOctetStream->value) {
          return [ MultipartFormDataAttrs::FormDataTypeFile->name => [
              $this->GetFormDataKey($formData) => [
                MultipartFormDataAttrs::FormDataFileName->name => $this->GetFormDataFilename($formData),
                MultipartFormDataAttrs::FormDataFileSize->name => $this->GetFileSize($formData[
                  MultipartFormDataAttrs::ContentBody->name
                ]),
                MultipartFormDataAttrs::FormDataFileType->name => pathinfo(
                  $this->GetFormDataFilename($formData), PATHINFO_EXTENSION
                ),
                MultipartFormDataAttrs::FormDataFileBody->name => base64_encode($formData[
                  MultipartFormDataAttrs::ContentBody->name
                ])
              ]
            ]
          ];
        } else
        if ($formData[MultipartFormDataAttrs::ContentType->name] === MultipartFormDataAttrs::ApplicationFormData->value) {
          return [ MultipartFormDataAttrs::FormDataTypeField->name => [
              $this->GetFormDataKey($formData) => $formData[
                MultipartFormDataAttrs::ContentBody->name
              ]
            ]
          ];
        }
      }
    );
  }

  private function ReadFileDefinedGrpups(
    array $formDataListArr = [
      MultipartFormDataAttrs::FormDataTypeField->name => [],
      MultipartFormDataAttrs::FormDataTypeFile->name => []
    ]
  ): void
  {
    foreach ($this->formDataList as $formDataList) {
      [ $formDataListKey ] = array_keys(
        $formDataList
      );

      $formDataListArr[$formDataListKey] = array_merge(
        $formDataListArr[$formDataListKey], $formDataList[ $formDataListKey]
      );
    }

    $this->formDataList = [];
    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeField->name => $formDataListArr[
        MultipartFormDataAttrs::FormDataTypeField->name
      ]
    ];

    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeFile->name => $formDataListArr[
        MultipartFormDataAttrs::FormDataTypeFile->name
      ]
    ];
  }

  private function ClearFormDataContentBody(): void
  {
    $this->formDataContentBody = [];
  }

  private function ReadPost(): void
  {
    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeField->name => $_POST
    ];
    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeFile->name => Utils::Map(
        $_FILES, function($file){
          $file = (object)$file;

          return [
            MultipartFormDataAttrs::FormDataFileName->name => $file->name,
            MultipartFormDataAttrs::FormDataFileSize->name => $file->size,
            MultipartFormDataAttrs::FormDataFileType->name => pathinfo(
              $file->name, PATHINFO_EXTENSION
            ),
            MultipartFormDataAttrs::FormDataFileBody->name => base64_encode(
              file_get_contents($file->tmp_name)
            )
          ];
        }
      )
    ];
  }

  private function ReadFile(): void
  { 
    $this->ReadFileStream();
    $this->ReadFileLoad();    
    $this->CloseFileStream();
    $this->ReadFileStreamJoin();
    $this->ReadFileDefinedExtract();
    $this->ReadFileDefinedGrpups();
    $this->ClearFormDataContentBody();
  }

  public function getFields(): FieldDataList
  {
    foreach ($this->formDataList as $formDataList) {
      [ $formDataListKey ] = array_keys(
        $formDataList
      );

      if ($formDataListKey === MultipartFormDataAttrs::FormDataTypeField->name){
        return FieldDataList::create(
          $formDataList[
            $formDataListKey
          ]
        );
      }
    }

    return FieldDataList::create();
  }

  public function getFiles(): FileDataList {
    foreach ($this->formDataList as $formDataList)
    {
      [ $formDataListKey ] = array_keys(
        $formDataList
      );

      if ($formDataListKey === MultipartFormDataAttrs::FormDataTypeFile->name){
        return FileDataList::create(
          $formDataList[
            $formDataListKey
          ]
        );
      }
    }

    return FileDataList::create();
  }  
}