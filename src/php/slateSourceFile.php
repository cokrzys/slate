<?php

/**

  slate | Source file and sp.source_file support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateSourceFile extends algaeTblBase
{
  
  public $source_data;
  public $file_format;
  public $filename;
  public $size_bytes;
  public $description;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->init();
  }
  
  /**
   * Initial default values.
   */
  public function init()
  // --------------------------------------------------------------------------
  {
    parent::init();
    $this->table_name = 'sp.source_file';
    $this->homepage = 'source_file.php';
    $this->browsepage = 'browse_source_file.php';
    $this->itemName = 'Source File';
    $this->itemNamePlural = 'Source Files';
    $this->filename = null;
    $this->size_bytes = null;
    $this->description = null;
    $this->source_data = new slateSourceData();
    $this->file_format = new refFileFormat();
  }
  
  public static function selectWithFormat($file_format_rowid_fk, $id, $default, $required = False)
  // --------------------------------------------------------------------------
  {
    $sf = new slateSourceFile();
    if ((isset($file_format_rowid_fk)) && ($file_format_rowid_fk > 0))
    {
      $sql = "SELECT filename, rowid 
                FROM $sf->table_name WHERE file_format_rowid_fk = " . 
                strval($file_format_rowid_fk) . " ORDER BY 1";
      return algaeForm::selectWithSQL($sql, $id, $default, $required, [], 600);
    }
  }
  
  public static function selectShapefile($id, $default, $required = False)
  // --------------------------------------------------------------------------
  {
    $format = new refFileFormat();
    $format->read_row_from_database_with_extension('shp');
    return slateSourceFile::selectWithFormat($format->rowid, $id, $default, $required);
  }
  
}




