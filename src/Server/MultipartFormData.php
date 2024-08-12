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
      MultipartFormDataAttrs::FormDataDefaultFile
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
      MultipartFormDataAttrs::FormDataRegExpEndFormData,
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
      MultipartFormDataAttrs::FormDataContentDisposition, 
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
      MultipartFormDataAttrs::FormDataContentType, 
      rtrim(
        $this->lineBuffer
      )
    );
  }

  private function AddReadFileContentType(
  ): void {
    $this->formDataContentType = MultipartFormDataAttrs::ApplicationOctetStream;
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
    if ($this->GetFormDataContentType() === MultipartFormDataAttrs::ApplicationFormData){
      $this->formDataContentBody[] = str_replace(
        MultipartFormDataAttrs::FormDataEndBody, "", $this->lineBuffer
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
    $this->formDataContentType = MultipartFormDataAttrs::ApplicationFormData;
  }

  private function ReadFileBof(
  ): bool {
    return preg_match_all(
      MultipartFormDataAttrs::FormDataRegExpStartFormData,
      rtrim($this->lineBuffer)
    );
  }

  private function ReadFileStream(
  ): void {
    $this->formDataStream = fopen(
      $this->formDataFile, MultipartFormDataAttrs::FormDataReadType
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
          MultipartFormDataAttrs::ContentDisposition => $this->GetReadFileContentDisposition(),
          MultipartFormDataAttrs::ContentType => $this->GetFormDataContentType(),
          MultipartFormDataAttrs::ContentBody => $this->GetFormDataContentBody()
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
        if ($formData === MultipartFormDataAttrs::ApplicationFormData) {
          return $formData;
        }

        return array_merge(
          $formData, [
            MultipartFormDataAttrs::ContentBody => implode(
              "", $formData[MultipartFormDataAttrs::ContentBody]
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
      explode(";", $formData[MultipartFormDataAttrs::ContentDisposition]),
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
      ";", $formData[MultipartFormDataAttrs::ContentDisposition]
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
        if ($formData[MultipartFormDataAttrs::ContentType] === MultipartFormDataAttrs::ApplicationOctetStream) {
          return [ MultipartFormDataAttrs::FormDataTypeFile => [
              $this->GetFormDataKey($formData) => [
                MultipartFormDataAttrs::FormDataFileName => $this->GetFormDataFilename($formData),
                MultipartFormDataAttrs::FormDataFileSize => $this->GetFileSize($formData[
                  MultipartFormDataAttrs::ContentBody
                ]),
                MultipartFormDataAttrs::FormDataFileType => pathinfo(
                  $this->GetFormDataFilename($formData), PATHINFO_EXTENSION
                ),
                MultipartFormDataAttrs::FormDataFileBody => base64_encode($formData[
                  MultipartFormDataAttrs::ContentBody
                ])
              ]
            ]
          ];
        } else
        if ($formData[MultipartFormDataAttrs::ContentType] === MultipartFormDataAttrs::ApplicationFormData) {
          return [ MultipartFormDataAttrs::FormDataTypeField => [
              $this->GetFormDataKey($formData) => $formData[
                MultipartFormDataAttrs::ContentBody
              ]
            ]
          ];
        }
      }
    );
  }

  private function ReadFileDefinedGrpups(
    array $formDataListArr = [
      MultipartFormDataAttrs::FormDataTypeField => [],
      MultipartFormDataAttrs::FormDataTypeFile => []
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
      MultipartFormDataAttrs::FormDataTypeField => $formDataListArr[
        MultipartFormDataAttrs::FormDataTypeField
      ]
    ];

    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeFile => $formDataListArr[
        MultipartFormDataAttrs::FormDataTypeFile
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
      MultipartFormDataAttrs::FormDataTypeField => $_POST
    ];
    $this->formDataList[] = [
      MultipartFormDataAttrs::FormDataTypeFile => Utils::Map(
        $_FILES, function($file){
          $file = (object)$file;

          return [
            MultipartFormDataAttrs::FormDataFileName => $file,
            MultipartFormDataAttrs::FormDataFileSize => $file->size,
            MultipartFormDataAttrs::FormDataFileType => pathinfo(
              $file->name, PATHINFO_EXTENSION
            ),
            MultipartFormDataAttrs::FormDataFileBody => base64_encode(
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

      if ($formDataListKey === MultipartFormDataAttrs::FormDataTypeField){
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

      if ($formDataListKey === MultipartFormDataAttrs::FormDataTypeFile){
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