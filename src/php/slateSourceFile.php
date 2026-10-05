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
  
  public function getShapefileExtents($filename)
  // --------------------------------------------------------------------------
  {
    global $app;
    $script = $app->getScriptsPath('shp_extents.sh');
    // $script = algaeCore::getFullPath($app->config->getAppConfigParameter($app->config->app_name, 'scriptsPath'), 'shp_extents.sh');    
    $output = array();
    exec($script . ' ' . $filename, $output);
    foreach ($output as $key => $line)
    {
      if ($key == 0)
      {
        $pieces = explode(" ", $line);
        if (count($pieces) == 4)
        {
          return [
            [floatval($pieces[0]), floatval($pieces[2])],
            [floatval($pieces[1]), floatval($pieces[3])]
          ];
        }
      }
    }
    return null;
  }
  
  public function getShapefileEPSG($filename)
  // --------------------------------------------------------------------------
  {
    global $app;
    $script = $app->getScriptsPath('shp_epsg.sh');
    // $script = algaeCore::getFullPath($app->config->getAppConfigParameter($app->config->app_name, 'scriptsPath'), 'shp_epsg.sh');
    $output = array();
    exec($script . ' ' . $filename, $output);
    foreach ($output as $key => $line)
    {
      if ($key == 0)
      {
        // ID["EPSG",26911]]
        $pieces = explode(",", $line);
        if (count($pieces) == 2)
        {
          return trim($pieces[1], ']');
        }
      }
    }
    return null;
  }
  
  /**
   * Get shapefile extents via an AJAX call.
   */
  public static function getShapefileExtentsViaAJAX()
  // --------------------------------------------------------------------------
  {
    //
    // ----- check required parameters
    //
    if (isset($_GET['source_file_rowid_fk']))
    {
      $s = new slateSourceFile();
      $s->read_row_from_database_with_rowid($_GET['source_file_rowid_fk']);
      $extents = $s->getShapefileExtents($s->filename);
      $epsg = $s->getShapefileEPSG($s->filename);
      //
      // ----- return the result
      //
      $results = array();
      $results['status'] = 'success';
      $results['min_x'] = $extents[0][0];
      $results['max_x'] = $extents[1][0];
      $results['min_y'] = $extents[0][1];
      $results['max_y'] = $extents[1][1];
      $results['epsg'] = $epsg;;
      // error_log('DEBUG: json_encode($results) = ' . json_encode($results), 0);
      echo json_encode($results);
      exit;
    }
    else
    {
      echo json_encode(array('status'=>'fail'));
      exit;
    }
  }
  
}




