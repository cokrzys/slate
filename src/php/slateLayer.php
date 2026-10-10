<?php

/**

  slate | Layer and sp.layer support.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateLayer extends algaeTblBase
{
  
  public $geoprocess;
  public $filename;
  public $resolution;
  public $num_cols;
  public $num_rows;
  public $num_nodata_cells;
  public $data_format;  // this is a string, 'byte', 'float32', etc.
  public $data_min;
  public $data_max;
  public $data_mean;
  public $data_stddev;
  public $data_q1;
  public $data_median;
  public $data_q3;
  public $sig_lower_cutoff;
  public $sig_upper_cutoff;
  public $last_updated_utc;
  
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
    $this->table_name = 'sp.layer';
    $this->homepage = 'layer.php';
    $this->geoprocess = new slateGeoprocess();
    $this->resolution = new refResolution();
    $this->filename = null;
    $this->num_cols = null;
    $this->num_rows = null;
    $this->num_nodata_cells = null;
    $this->data_format = null;
    $this->data_min = null;
    $this->data_max = null;
    $this->data_mean = null;
    $this->data_stddev = null;
    $this->data_q1 = null;
    $this->data_median = null;
    $this->data_q3 = null;
    $this->sig_lower_cutoff = null;
    $this->sig_upper_cutoff = null;
    $this->last_updated_utc = null;
  }
 
  /**
   * Get links to act on the item.
   * @return string  HTML string with the links.
   * {@inheritDoc}
   * @see algaeTblBase::getActionLinks()
   */
  public function getActionLinks($openInNewTab=False)
  // --------------------------------------------------------------------------
  {
    $html = '';
    return $html;
  }
  
  public function readRowFromDatabaseWithGeoprocess($geoprocess)
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql();
    $sql .= " WHERE {$this->geoprocess->table_name}.rowid = $1";
    $this->read_row_from_database_with_sql($sql, array($geoprocess->rowid));
    if ($this->rowid > 0)
    {
      return True;
    }
    return False;
  }
  
  /**
   * Read a layer for a geoprocess and create if it doesn't exist.
   */
  public function readOrCreateLayerForGeoprocess()
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql();
    $sql .= " WHERE {$this->geoprocess->table_name}.rowid = $1 AND {$this->table_name}.filename = $2";
    $this->read_row_from_database_with_sql($sql, array($this->geoprocess->rowid, $this->filename));
    if ( ($this->rowid == null) || ($this->rowid <= 0) )
    {
      if ($this->okToWrite())
      {
        $this->writeToDatabase();
      }
    }
  }
  
  public function getFullyPathedFilename()
  // --------------------------------------------------------------------------
  {
    return algaeCore::getFullPath($this->geoprocess->getDirectory(), $this->filename);
  }
  
  protected function getFullyPathedFilenameWithSuffix($suffix)
  // --------------------------------------------------------------------------
  {
    $filename = pathinfo($this->filename, PATHINFO_FILENAME) . $suffix;
    return algaeCore::getFullPath($this->geoprocess->getDirectory(), $filename);
  }
  
  public function getFullyPathedColoredFilename()
  // --------------------------------------------------------------------------
  {
    global $app;
    return $this->getFullyPathedFilenameWithSuffix($app->config->colored_suffix);
  }
  
  public function getFullyPathedThumbnailFilename()
  // --------------------------------------------------------------------------
  {
    global $app;
    return $this->getFullyPathedFilenameWithSuffix($app->config->thumbnail_suffix);
  }
  
  public function getFullyPathedAnnotatedFilename()
  // --------------------------------------------------------------------------
  {
    global $app;
    return $this->getFullyPathedFilenameWithSuffix($app->config->annotated_suffix);
  }
  
  public function getFullyPathedOverlayFilename()
  // --------------------------------------------------------------------------
  {
    global $app;
    return $this->getFullyPathedFilenameWithSuffix($app->config->overlay_suffix);
  }
  
  protected function getFullyPathedRasterForWebMap()
  // --------------------------------------------------------------------------
  {
    $filename = $this->getFullyPathedColoredFilename();
    if (file_exists($filename))
    {
      return $filename;
    }
    return $this->getFullyPathedFilename();
  }
  
  /**
   * Tabular report of records.
   * {@inheritDoc}
   * @see algaeTblBase::reportRecords()
   */
  public function reportRecords($tableId = 'slateLayersTable', $whereClause = '', $maxRecords = 10000)
  // --------------------------------------------------------------------------
  {
    global $app;
    $sql = $this->get_sql(True);
    $sql .= $whereClause;
    $data = algaeDB::getArray($sql, array());
    if (count($data) > 0)
    {
      //
      // ----- initial the table
      //
      $tableId = $this->getDefaultRecordsTableId($tableId);
      algaeTable::initTablesorterJavascript($tableId, '[[1,0]]', True, "headers: {5: {sorter:'milDate'} }");
      algaeTable::start($tableId, 'tablesorter', 'width:100%;');
      //
      // ----- table header
      //
      $header_array = array(
        array('', '10%', False),
        array('Layer', '20%'),
        array('Data Group', '10%'),
        array('GeoProcess', '30%'),
        array('Resolution', '10%'),
        array('Last Updated (UTC)', '15%')
      );
      algaeTable::writeHeader($header_array, True);
      //
      // ----- loop through the results
      //
      foreach ($data as $row)
      {
        $l = new slateLayer();
        $l->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        
        $thumbnail = $l->getFullyPathedThumbnailFilename();
        if (file_exists($thumbnail))
        {
          $b64image = base64_encode(file_get_contents($thumbnail));
          algaeTable::writeData("<img src = 'data:image/png;base64,$b64image' width='75px'>", False, '', '', 'align_center');
        }
        else 
        {
          algaeTable::writeData('');
        }
        
        $name = pathinfo($l->filename, PATHINFO_FILENAME);
        algaeTable::writeData($l->getHomepageLink($name) . $app->config->menu_separator . algaeFile::getDownloadLink($l->getFullyPathedFilename()), False);
        algaeTable::writeData(algaeCore::getColorBlock($l->geoprocess->data_group->html_color, True, $l->geoprocess->data_group->name), False);
        algaeTable::writeData($l->geoprocess->getHomepageLink(), False);
        algaeTable::writeData($l->resolution->name);
        algaeTable::writeData($l->last_updated_utc);
        echo '</tr>';
      }
      algaeTable::end();
    }
    else 
    {
      $gp = new slateGeoprocess();
      $link = $app->getPageLink($gp->select_geoprocess_page, 'GeoProcess', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
      if (strlen($link) == 0) $link = 'GeoProcess';
      echo 'Add layers by creating a new ', $link, '.<p />';
    }
  }
  
  /**
   * Report records for the current project.
   */
  public function reportRecordsForCurrentProject()
  // --------------------------------------------------------------------------
  {
    global $app;
    $project_rowid_fk = $app->getCurrentProjectRowid();
    if ($project_rowid_fk > 0)
    {
      $where = ' WHERE sp.project.rowid = ' . $project_rowid_fk;
      $where .= ' AND ' . $this->table_name . '.resolution_rowid_fk = ';
      $where .= "(SELECT rowid FROM ref.resolution WHERE name = '" . $app->getResolutionName() . "')";
      $this->reportRecords('slateLayersTable', $where);
    }
  }
  
  /**
   * Report raster statistics using GDAL to read the source image.
   */
  public function reportGDALInfo()
  // --------------------------------------------------------------------------
  {
    $outputFile = $this->getFullyPathedFilename() . '.gdalinfo';
    exec('gdalinfo -noct ' . $this->getFullyPathedFilename() . ' > ' . $outputFile);
    algaeFile::showTextFile($outputFile, 'Metadata from gdalinfo:');
  }
  
  protected function writeFileDownloadPair($label, $filename)
  // --------------------------------------------------------------------------
  {
    global $app;
    if (file_exists($filename))
    {
      algaeTable::writeTwoColumns($label, $filename . $app->config->menu_separator .
        algaeFile::getDownloadLink($filename), False);
    }
  }
  
  /**
   * Report overall details for the object.
   */
  protected function reportOverallDetailsWithMap($map)
  // --------------------------------------------------------------------------
  {
    global $app;
    echo $this->getActionLinks(), '<p />';
    
    $thumbnail = $this->getFullyPathedThumbnailFilename();
    if (file_exists($thumbnail))
    {
      $b64image = base64_encode(file_get_contents($thumbnail));
      echo "<img src = 'data:image/png;base64,$b64image'><p />";
    }
    
    algaeTable::start('layerDetailsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Data', '<b>' . $this->filename . '</b>' . $app->config->menu_separator .
      algaeFile::getDownloadLink($this->getFullyPathedFilename()), False);
    #
    #
    #
    $this->writeFileDownloadPair('Thumbnail', $this->getFullyPathedThumbnailFilename());
    $this->writeFileDownloadPair('Colored', $this->getFullyPathedColoredFilename());
    $this->writeFileDownloadPair('Overlay', $this->getFullyPathedOverlayFilename());
    algaeTable::writeTwoColumns('Last Updated', $this->last_updated_utc);
    #
    #
    #
    /*
    algaeTable::writeTwoColumns('Fully Pathed Filename', $this->getFullyPathedFilename());
    if (file_exists($this->getFullyPathedFilename() . '.xml'))
    {
      algaeTable::writeTwoColumns('XML Legend', $this->filename . '.xml' . $app->config->menu_separator .
        algaeFile::getDownloadLink($this->getFullyPathedFilename() . '.xml'), False);
    }
    */
    algaeTable::writeTwoColumns('GeoProcess', $this->geoprocess->getHomepageLink() . $this->geoprocess->getRunLink(), False);
    algaeTable::writeTwoColumns('Data Group', algaeCore::getColorBlock($this->geoprocess->data_group->html_color, True, $this->geoprocess->data_group->name), False);
    algaeTable::writeTwoColumns('Resolution', $this->resolution->name);
    algaeTable::writeTwoColumns('Map File', $map->getMapFilename() . $app->config->menu_separator . $app->getPageLink('view_text_file.php?filename=' . urlencode($map->getMapFilename()), 'View', algaeAccess::ROLE_READ, $app->config->app_name, '', True), False);
    algaeTable::writeTwoColumns('Created', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
  }
  
  /**
   * Report cached stats read from the database.
   */
  protected function reportCachedStats()
  // --------------------------------------------------------------------------
  {
    echo $this->geoprocess->name, '<p />';
    echo 'Cached stats last updated ', $this->timestamp_modified_utc, ' UTC.</p>';
    algaeTable::start('statsTable', 'algae_table', 'width:30%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Num Cols', $this->num_cols);
    algaeTable::writeTwoColumns('Num Rows', $this->num_rows);
    $num_cells = $this->num_cols * $this->num_rows;
    algaeTable::writeTwoColumns('Num Cells', algaeCore::getFormattedNumber($num_cells, 0, null));
    algaeTable::writeTwoColumns('Num NoData Cells', algaeCore::getFormattedNumber($this->num_nodata_cells, 0, null));
    $percent_nodata_cells = 0;
    if ($this->num_nodata_cells != null)
    {
      $percent_nodata_cells = ($this->num_nodata_cells / $num_cells) * 100;
    }
    algaeTable::writeTwoColumns('Percent NoData', algaeCore::getFormattedPercentage($percent_nodata_cells));
    algaeTable::writeTwoColumns('Data Format', $this->data_format);
    algaeTable::writeTwoColumns('Data Min', algaeCore::getFormattedNumber($this->data_min, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Data Max', algaeCore::getFormattedNumber($this->data_max, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Data Mean', algaeCore::getFormattedNumber($this->data_mean, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Data StdDev', algaeCore::getFormattedNumber($this->data_stddev));
    $cv = null;
    if ( ($this->data_mean != 0) && ($this->data_mean != null) && ($this->data_stddev != null) )
    {
      $cv = $this->data_stddev / $this->data_mean;
    }
    algaeTable::writeTwoColumns('Data CV', algaeCore::getFormattedNumber($cv, 3));
    algaeTable::writeTwoColumns('Data Q1', algaeCore::getFormattedNumber($this->data_q1, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Data Median', algaeCore::getFormattedNumber($this->data_median, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Data Q3', algaeCore::getFormattedNumber($this->data_q3, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Signature Lower Cutoff', algaeCore::getFormattedNumber($this->sig_lower_cutoff, $this->geoprocess->num_decimals, null));
    algaeTable::writeTwoColumns('Signature Upper Cutoff', algaeCore::getFormattedNumber($this->sig_upper_cutoff, $this->geoprocess->num_decimals, null));
    algaeTable::end();
  }
  
  /**
   * Report details for the object.
   * {@inheritDoc}
   * @see algaeTblBase::reportDetails()
   */
  protected function reportDetails()
  // --------------------------------------------------------------------------
  {
    global $app;
    $map = new algaeMap();
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#overview_tab', 'Overview'),
      array('#stats_tab', 'Stats'),
      array('#map_tab', 'Map'),
      array('#classes_tab', 'Classes'),
      array('#signatures_tab', 'Signatures'),
      array('#info_tab', 'Info')
      # array('#map_file_tab', 'Map File')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetailsWithMap($map);
    echo '</div>';
    //
    // ----- map_tab
    //
    echo '<div id="map_tab">';
    
    echo algaeForm::button('invalidate', 'Invalidate', 'map.invalidateSize(true);'), '<p />';
    
    $study_area = new slateStudyArea();
    $study_area->readRowFromDatabaseWithProjectRowid($app->getCurrentProjectRowid());
    $study_area->setMapBounds($map);
    $map->startMapFile();
    $name = pathinfo($this->filename, PATHINFO_FILENAME);
    $map->addRasterLayer($this->getFullyPathedRasterForWebMap(), $name, $name, 1, $study_area->srid_fk, 70);
    $map->endMapFile();
    $map->show();
    echo '</div>';
    //
    // ----- stats_tab
    //
    echo '<div id="stats_tab">';
    $this->reportCachedStats();
    echo '</div>';
    //
    // ----- classes_tab
    //
    echo '<div id="classes_tab">';
    $c = new slateClass();
    $c->reportRecordsForLayer($this->rowid);
    echo '</div>';
    //
    // ----- signatures_tab
    //
    echo '<div id="signatures_tab">';
    slateSignature::reportSignaturesForLayer($this->rowid);
    echo '</div>';
    //
    // ----- info_tab
    //
    echo '<div id="info_tab">';
    $this->reportGDALInfo();
    echo '</div>';
    //
    // ----- map_file_tab
    //
    /*
    echo '<div id="map_file_tab">';
    algaeFile::showTextFile($map->getMapFilename());
    echo '</div>';
    */
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  /**
   * Delete all files associated with the layer.
   * @return number The number of errors, 0 if none.
   */
  protected function deleteLayerFiles()
  // --------------------------------------------------------------------------
  {
    $num_errors = 0;
    $layerFile = new slateLayer();
    $sql = $layerFile->getSQL();
    $sql .= " WHERE $layerFile->table_name.layer_rowid_fk = $1";
    $db = algaeDB::connect();
    if ($db)
    {
      $parms = array($this->rowid);
      $result = pg_query_params($db, $sql, $parms);
      if (! $result)
      {
        algaeDB::errorWithSQL($sql, $parms);
      }
      //
      // ----- loop through the results
      //
      while ($row = pg_fetch_array($result))
      {
        $layerFile->init();
        $layerFile->readRowFromDatabase($row);
        $num_errors += $layerFile->delete();
      }
      algaeDB::close($db, $result);
    }
    return $num_errors;
  }
  
  /**
   * Delete the layer.
   * {@inheritDoc}
   * @see algaeTblBase::delete()
   */
  public function delete()
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_errors = 0;
    //
    // ----- delete layer files
    //
    if ($num_errors == 0)
    {
      $num_errors += $this->deleteLayerFiles();
    }
    //
    // ----- delete the layer
    //
    if ($num_errors == 0)
    {
      if (algaeDB::deleteFromTable($this->table_name, 'rowid', $this->rowid))
      {
        $app->successMessage('Layer ' . $this->name . ' deleted from ' . $this->table_name . '.');
        // echo 'Goto the ', $this->project->getHomepageLink(), ' homepage.<p />';
      }
      else
      {
        $num_errors += 1;
      }
    }
    if ($num_errors == 0) $this->deleted = True;
    return $num_errors;
  }
  
  public function getZScore($val)
  // --------------------------------------------------------------------------
  {
    if ( ($this->data_min != null) && ($this->data_min != null) )
    {
      return ($val - $this->data_min) / ($this->data_max - $this->data_min); 
    }
    else 
    {
      return null;
    }
  }
  
  public function getFormattedDisplayValue($val)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($val != null)
    {
      $str = '';
      if ( ($this->geoprocess->data_type->name == 'Categorical') || 
           ($this->geoprocess->data_type->name == 'Mask') )
      {
        $str = algaeCore::getFormattedNumber($val, 0, null, '');
        $c = new slateClass();
        if ($c->readRowFromDatabaseWithLayerAndValue($this->rowid, $val))
        {
          if (strlen($c->description) > 0)
          {
            $str .= $app->getDetailString($c->description);
          }
          else 
          {
            $str .= $app->getDetailString($c->code);
          }
        }
      }
      else 
      {  
        $str = algaeCore::getFormattedNumber($val, $this->geoprocess->num_decimals, null, '');
      }
      if ($this->geoprocess->units->abbreviation != null)
      {
        $str .= ' ' . $this->geoprocess->units->abbreviation;
      }
      if ($this->geoprocess->units->abbreviation == '°C')
      {
        $deg_f = ($val * 1.8) + 32;
        $str .= $app->getDetailString(algaeCore::getFormattedNumber($deg_f, $this->geoprocess->num_decimals, null, '') . ' °F');
      }
      return $str;
    }
    return '';
  }
  
}





