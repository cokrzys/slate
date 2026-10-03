<?php

/**

  slate | Study area and support for the sp.study_area table.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateStudyArea extends algaeTblBase
{
  
  public $project;
  public $resolution;
  public $source_file;
  public $min_x;
  public $max_x;
  public $min_y;
  public $max_y;
  public $buffer;
  public $srid_fk;
  public $user;
  public $min_lat;
  public $min_long;
  public $max_lat;
  public $max_long;
  public $geoprocess;
  public $place;
  
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
    $this->table_name = 'sp.study_area';
    $this->homepage = 'project.php';
    $this->editpage = 'edit_study_area.php';
    $this->project = new slateProject();
    $this->resolution = new refResolution();
    $this->source_file = new slateSourceFile();
    $this->geoprocess = new slateGeoProcess();
    $this->place = new slatePlace();
    $this->user = new algaeTblCoreUser();
    $this->min_x = null;
    $this->max_x = null;
    $this->min_y = null;
    $this->max_y = null;
    $this->buffer = null;
    $this->srid_fk = null;
    $this->min_lat = null;
    $this->min_long = null;
    $this->max_lat = null;
    $this->max_long = null;
  }
  
  /**
   * Get links to act on the item.
   * @return string  HTML string with the links.
   */
  public function getActionLinks($openInNewTab=False)
  // --------------------------------------------------------------------------
  {
    $html = '';
    $vars = '?rowid=' . $this->rowid . '&project_rowid_fk=' . $this->project->rowid;
    if ($this->numVariableErrors() == 0)
    {
      global $app;
      $html = $app->getPageLink($this->editpage . $vars, 'Edit', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
      if (strlen($this->deletepage) > 0)
      {
        $html .= $app->config->menu_separator;
        $html .= $app->getPageLink($this->deletepage . '?rowid=' . $this->rowid, 'Delete', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
      }
    }
    return $html;
  }
  
  protected function readMaskFilename()
  // --------------------------------------------------------------------------
  {
    
  }
  
  public function readRowFromDatabaseWithProjectRowid($project_rowid_fk)
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql();
    $sql .= ' WHERE sp.project.rowid = $1';
    return $this->read_row_from_database_with_sql($sql, array($project_rowid_fk));
  }
  
  protected function getNumValidShapefiles($project_rowid_fk)
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT COUNT(*) AS num
            FROM sp.shapefile
            WHERE project_rowid_fk = $1 AND geometry_type_rowid_fk = (SELECT rowid FROM ref.geometry_type WHERE name = '3DPolygon')";
    return algaeDB::getScalarInteger($sql, array($project_rowid_fk), 0);
  }
  
  /**
   * Process a form that's been submitted.
   */
  public function processForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    if (isset($_POST['submit']))
    {
      if (algaeForm::validTokens(algaeForm::getDefaultToken($this)))
      {
        if (isset($_POST['rowid']))
        {
          $this->rowid = algaeForm::cleanInput($_POST['rowid']);
        }
        $this->post_control_data();
        $this->project->rowid = algaeForm::cleanInput($_POST['project_rowid_fk']);
        $this->project->read_row_from_database_with_rowid($this->project->rowid);
        if (isset($_REQUEST['rowid']))
        {
          if ($this->ok_to_update())
          {
            if ($this->update())
            {
              $this->project->read_row_from_database_with_rowid($this->project->rowid);
              $app->successMessage('Study Area for ' . $this->project->name . ' successfully updated.');
              $this->updated = True;
              return True;
            }
            else
            {
              $app->errorMessage('Problem updating the study area.');
            }
          }
        }
        else
        {
          if ($this->ok_to_insert())
          {
            if ($this->insert())
            {
              $app->successMessage('Study area successfully added.');
              $this->added = True;
              $this->project->readRowFromDatabaseWithRowid($this->project->rowid);
              echo 'Goto the ', $this->project->getHomepageLink(), ' homepage.<p />';
              return True;
            }
            else
            {
              $app->errorMessage('Problem adding the study.');
            }
          }
        }
      }
    }
    return False;
  }
  
  /**
   * Show form to edit a record.
   */
  public function showForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)
    {
      $this->project->read_row_from_database_with_rowid($this->project->rowid);
      $f = new algaeForm();
      //
      // ----- get data if editing
      //
      if (isset($_REQUEST['rowid']))
      {
        $this->read_row_from_database_with_rowid($_REQUEST['rowid']);
      }
      //
      // ----- start the form
      //
      $f->startForm(algaeForm::getDefaultToken($this));
      echo '<input type="hidden" name="project_rowid_fk" value="', $this->project->rowid, '" />';
      if ($this->rowid > 0)
      {
        echo '<input type="hidden" name="rowid" value="', $this->rowid, '" />';
      }
      echo 'Study Area setup for ', $this->project->getHomepageLink(), '.<p />';
      //
      //  -----  shapefile
      //
      echo 'Study area shapefile &nbsp;&nbsp;',
      slateSourceFile::selectShapefile($this->get_control_id('source_file_rowid_fk'), '', False), '<p />';
      echo algaeForm::button('defaults', 'Get Defaults', 'setupDefaults();'), '<p />';
      //
      // ----- table to keep items aligned
      //
      algaeTable::start('formTable', 'algae_form_table', '');
      algaeTable::writeHeader(array(), False);
      //
      // ----- coordinate system
      //
      algaeTable::writeTwoColumns('Coordinate System EPSG Code',
        algaeForm::inputText('srid_fk', $this->srid_fk, 10, algaeForm::REQUIRED) . '&nbsp;&nbsp;' .
        $app->getPageLink('browse_coordinate_systems.php', 'Coordinate Systems', algaeAccess::ROLE_READ, $app->config->app_name, '', True),
        False);
      //
      // ----- reference place
      //
      /*
      $sql = "SELECT name
              FROM sp.place
              WHERE project_rowid_fk = $1";
      $sql = str_replace('$1', $this->project->rowid, $sql);
      algaeTable::writeTwoColumns('Reference Place', algaeForm::selectWithSQL($sql, 
        $this->get_control_id('place_rowid_fk'), $this->place->name), False);
      algaeTable::end();
      */
      //
      // ----- 
      //
      $width = 15;
      algaeTable::start('minMaxTable', 'algae_form_table', '');
      algaeTable::writeHeader(array(), False);
      echo '<tr>';
      algaeTable::writeData('X Min' . $app->config->menu_separator . 'X Max', False);
      algaeTable::writeData(algaeForm::inputText('min_x', $this->min_x, $width), False);
      algaeTable::writeData(algaeForm::inputText('max_x', $this->max_x, $width), False);
      echo '</tr>';
      echo '<tr>';
      algaeTable::writeData('Y Min' . $app->config->menu_separator . 'Y Max', False);
      algaeTable::writeData(algaeForm::inputText('min_y', $this->min_y, $width), False);
      algaeTable::writeData(algaeForm::inputText('max_y', $this->max_y, $width), False);
      echo '</tr>';
      algaeTable::end();
      //
      // -----
      //
      algaeTable::start('advancedTable', 'algae_form_table', '');
      algaeTable::writeHeader(array(), False);
      
      algaeTable::writeTwoColumns('Resolution', algaeForm::selectWithTableAndFieldWithRowid('ref.resolution', 'name',
        $this->get_control_id('resolution_rowid_fk'), $this->resolution->name, True), False);
      
      algaeTable::writeTwoColumns('Buffer', algaeForm::inputText('buffer', $this->buffer), False);
      //
      // ----- study area mask
      //
      $sql = "SELECT name, rowid FROM sp.geoprocess WHERE project_rowid_fk = $1 AND php_class = 'slateGeoProcMask'";
      $sql = str_replace('$1', $this->project->rowid, $sql);
      algaeTable::writeTwoColumns('Mask', algaeForm::selectWithSQL($sql, 
        $this->get_control_id('geoprocess_rowid_fk'), $this->geoprocess->name), False);
      algaeTable::end();
      //
      //
      //
      echo '<p />';
      $f->endForm('Save', False);
      echo '<p /><br />';
    }
  }
  
  public function getNumCols($resolution)
  // --------------------------------------------------------------------------
  {
    if ($resolution != null)
    {
      return ($this->max_x - $this->min_x) / $resolution;
    }
    return null;
  }
  
  public function getNumRows($resolution)
  // --------------------------------------------------------------------------
  {
    if ($resolution != null)
    {
      return ($this->max_y - $this->min_y) / $resolution;
    }
    return null;
  }
  
  public function getNumCells($resolution)
  // --------------------------------------------------------------------------
  {
    return $this->getNumCols($resolution) * $this->getNumRows($resolution);
  }
  
  public function getColsRowsCellsString($resolution)
  // --------------------------------------------------------------------------
  {
    $str = '';
    $str .= algaeCore::getFormattedNumber($this->getNumCols($resolution), 0, 0, '');
    $str .= ' x ';
    $str .= algaeCore::getFormattedNumber($this->getNumRows($resolution), 0, 0, '');
    $str .= ' | ';
    $str .= algaeCore::getFormattedNumber($this->getNumCells($resolution), 0, 0);
    $str .= '';
    return $str;
  }
  
  /**
   * Get a good thumbnail size for on-screen preview respecting ideal width and height.
   * @return number[]
   */
  public function getThumbnailSize()
  // --------------------------------------------------------------------------
  {
    global $app;
    $ncols = $this->getNumCols($this->resolution->cell_size_x);
    $nrows = $this->getNumRows($this->resolution->cell_size_y);
    // echo 'DEBUG: Original aspect = ', $ncols / $nrows, '<p />';
    if (($ncols != null) && ($nrows != null))
    {
      $x2 = ceil(($ncols / $nrows) * $app->thumbnailBestHeight);
      if ($x2 <= $app->thumbnailBestWidth)
      {
        // echo 'DEBUG: Height best aspect = ', $x2 / $app->thumbnailBestHeight, '<p />';
        // echo 'DEBUG: ', $x2, ' x ', $app->thumbnailBestHeight, '<p />';
        return array($x2, $app->thumbnailBestHeight);
      }
      else 
      {
        $y2 = ceil(($nrows / $ncols) * $app->thumbnailBestWidth);
        // echo 'DEBUG: Width best aspect = ', $app->thumbnailBestWidth / $y2, '<p />';
        // echo 'DEBUG: ', $app->thumbnailBestWidth, ' x ', $y2, '<p />';
        return array($app->thumbnailBestWidth, $y2);
      }
    }
    return null;
  }
  
  /*
   * 
   */
  public function reportLatLongBounds()
  // --------------------------------------------------------------------------
  {

    echo '<h2>Lat-Long Bounds (WGS84, Decimal Degrees with Buffer)</h2>';
    algaeTable::start('latLongBoundsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array('Coordinate', 'Minimum', 'Maximum'), False);
    echo '<tr>';
    algaeTable::writeData('Latitude');
    algaeTable::writeData(algaeCore::getFormattedNumber($this->min_lat, 4, null, ''));
    algaeTable::writeData(algaeCore::getFormattedNumber($this->max_lat, 4, null, ''));
    echo '</tr>';
    echo '<tr>';
    algaeTable::writeData('Longitude');
    algaeTable::writeData(algaeCore::getFormattedNumber($this->min_long, 4, null, ''));
    algaeTable::writeData(algaeCore::getFormattedNumber($this->max_long, 4, null, ''));
    echo '</tr>';
    algaeTable::end();
    echo '<p />';
    echo '<div class="footnote">';
    echo 'Buffer = ', $this->buffer, ' meters.<p />';
    echo '</div>';
  }
  
  public function reportDetails()
  // --------------------------------------------------------------------------
  {
    global $app;
    echo $this->getActionLinks(), '<p />';
    algaeTable::start('studyAreaDetailsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Project', $this->project->name);
    algaeTable::writeTwoColumns('Owner', $this->user->username);
    algaeTable::writeTwoColumns('EPSG Code', $this->srid_fk . '&nbsp;&nbsp;' .
      $app->getPageLink('browse_coordinate_systems.php', 'Coordinate Systems', algaeAccess::ROLE_READ, $app->config->app_name, '', True), False);
    algaeTable::writeTwoColumns('Coordinate System', $this->coord_system_name);
    algaeTable::writeTwoColumns('Shapefile', $this->shapefile->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Mask', $this->geoprocess->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Reference Place', $this->place->getHomepageLink(), False);
    algaeTable::writeTwoColumns('X Min | Max', algaeCore::getFormattedNumber($this->min_x, 0) . ' | ' . algaeCore::getFormattedNumber($this->max_x, 0));
    algaeTable::writeTwoColumns('Y Min | Max', algaeCore::getFormattedNumber($this->min_y, 0) . ' | ' . algaeCore::getFormattedNumber($this->max_y, 0));
    algaeTable::writeTwoColumns('Resolution', $this->resolution->getHomepageLink(), False);
    
    algaeTable::writeTwoColumns('ncols x nrows | ncells', $this->getColsRowsCellsString($this->resolution->cell_size_x));
    
    /*
    $x_size = (($this->max_x - $this->min_x) / $this->getNumCols($this->low_resolution)) / 1000;
    $y_size = (($this->max_y - $this->min_y) / $this->getNumRows($this->low_resolution)) / 1000;
    $size_str = algaeCore::getFormattedNumber($x_size) . $app->config->menu_separator . algaeCore::getFormattedNumber($y_size);
    algaeTable::writeTwoColumns('Approximate Cell Size (km) Low Resolution X | Y', $size_str, False);
    
    $x_size = (($this->max_x - $this->min_x) / $this->getNumCols($this->medium_resolution)) / 1000;
    $y_size = (($this->max_y - $this->min_y) / $this->getNumRows($this->medium_resolution)) / 1000;
    $size_str = algaeCore::getFormattedNumber($x_size) . $app->config->menu_separator . algaeCore::getFormattedNumber($y_size);
    algaeTable::writeTwoColumns('Approximate Cell Size (km) Medium Resolution X | Y', $size_str, False);
    
    $x_size = (($this->max_x - $this->min_x) / $this->getNumCols($this->high_resolution)) / 1000;
    $y_size = (($this->max_y - $this->min_y) / $this->getNumRows($this->high_resolution)) / 1000;
    $size_str = algaeCore::getFormattedNumber($x_size) . $app->config->menu_separator . algaeCore::getFormattedNumber($y_size);
    algaeTable::writeTwoColumns('Approximate Cell Size (km) High Resolution X | Y', $size_str, False);
    */
    
    
    algaeTable::writeTwoColumns('Buffer', algaeCore::getFormattedNumber($this->buffer, 0, 0, ''));
    algaeTable::writeTwoColumns('Created', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
    // $this->readLatLongBounds();
    // $this->reportLatLongBounds();
    // $this->reportApproximateFileSizes();
    $this->getThumbnailSize();
  }
  
  public function reportDetailsForProject($project_rowid_fk)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->readRowFromDatabaseWithProjectRowid($project_rowid_fk))
    {
      $this->reportDetails();
    }
    else 
    {
      if ( ($this->getNumValidShapefiles($project_rowid_fk) > 0) || (True == True) )
      {
        $setup_link = $app->getPageLink($this->editpage . '?project_rowid_fk=' . $project_rowid_fk,
          'Setup the Study Area', algaeAccess::ROLE_WRITE, $app->config->app_name, '') . '<p />';
        echo $setup_link;
      }
      else 
      {
        $s = new slateShapefile();
        echo '<a href="', $s->selectpage, '?project_rowid_fk=', $project_rowid_fk, '">Upload a Shapefile</a> containing the Study Area then continue the Study Area Setup.<p />';
      }
    }
  }
  
  /**
   * Get study area  defaults typically via an AJAX call.
   */
  public static function getStudyAreaDefaults()
  // --------------------------------------------------------------------------
  {
    //
    // ----- check required parameters
    //
    if (isset($_GET['shapefile_rowid_fk']))
    {
      $s = new slateShapefile();
      $s->readRowFromDatabaseWithRowid($_GET['shapefile_rowid_fk']);
      $defaults = $s->setupDefaults(False);
      // error_log('DEBUG: $defaults[0] = ' . $defaults[0], 0);
      //
      // ----- return the result
      //
      $results = array();
      $results['status'] = 'success';
      $results['srid_fk'] = $s->srid_fk;
      $results['min_x'] = $defaults[0];
      $results['max_x'] = $defaults[1];
      $results['min_y'] = $defaults[2];
      $results['max_y'] = $defaults[3];
      $results['high_resolution'] = $defaults[4];
      $results['medium_resolution'] = $defaults[5];
      $results['low_resolution'] = $defaults[6];
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
  
  /**
   * Write study area information to a JSON file.
   */
  public function writeToJSON()
  // --------------------------------------------------------------------------
  {
    $this->readRowFromDatabaseWithRowid($this->rowid);
    $this->shapefile->readRowFromDatabaseWithRowid($this->shapefile->rowid);
    $study_area = array();
    $study_area['project_rowid_fk'] = $this->project->rowid;
    $study_area['project_name'] = $this->project->name;
    $study_area['study_area_rowid_fk'] = $this->rowid;
    $study_area['srid_fk'] = $this->srid_fk;
    $study_area['shapefile'] = $this->shapefile->source_filename;
    // echo json_encode($study_area, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
    
    $fp = fopen($this->project->folder . 'study_area.json', 'w');
    fwrite($fp, json_encode($study_area, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
    fclose($fp);
  }
  
  /**
   * Show what's being deleted.
   * {@inheritDoc}
   * @see algaeTblBase::showWhatsBeingDeleted()
   */
  public function showWhatsBeingDeleted()
  // --------------------------------------------------------------------------
  {
    $num_geoprocesses = algaeDB::getScalarInteger("SELECT COUNT(rowid) FROM sp.geoprocess WHERE study_area_rowid_fk = $1", array($this->rowid), 0);
    if ($num_geoprocesses > 0)
    {
      echo $num_geoprocesses, ' geoprocess(es) will be deleted.<p />';
    }
    $num_layers = algaeDB::getScalarInteger("SELECT COUNT(rowid) FROM sp.layer WHERE study_area_rowid_fk = $1", array($this->rowid), 0);
    if ($num_layers > 0)
    {
      echo $num_layers, ' layer(s) will be deleted.<p />';
    }
  }
  
  /**
   * Delete all layers associated with the study area.
   * @return number The number of errors, 0 if none.
   */
  protected function deleteLayers()
  // --------------------------------------------------------------------------
  {
    $num_errors = 0;
    $layer = new slateLayer();
    $sql = $layer->getSQL();
    $sql .= " WHERE $layer->table_name.study_area_rowid_fk = $1";
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
        $layer->init();
        $layer->readRowFromDatabase($row);
        $num_errors += $layer->delete();
      }
      algaeDB::close($db, $result);
    }
    return $num_errors;
  }
  
  /**
   * Delete all geoprocesses associated with the study area.
   * @return number The number of errors, 0 if none.
   */
  protected function deleteGeoprocesses()
  // --------------------------------------------------------------------------
  {
    $num_errors = 0;
    $gp = new slateGeoProcess();
    $sql = $gp->getSQL();
    $sql .= " WHERE $gp->table_name.study_area_rowid_fk = $1";
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
        $gp->init();
        $gp->readRowFromDatabase($row);
        $num_errors += $gp->delete();
      }
      algaeDB::close($db, $result);
    }
    return $num_errors;
  }
  
  /**
   * Delete the study area.
   * {@inheritDoc}
   * @see algaeTblBase::delete()
   */
  public function delete()
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_errors = 0;
    //
    // ----- delete layers
    //
    if ($num_errors == 0)
    {
      $num_errors += $this->deleteLayers();
    }
    //
    // ----- delete geoprocesses
    //
    if ($num_errors == 0)
    {
      $num_errors += $this->deleteGeoprocesses();
    }
    //
    // ----- delete study area
    //
    if ($num_errors == 0)
    {
      if (algaeDB::deleteFromTable($this->table_name, 'rowid', $this->rowid))
      {
        $app->successMessage('Study area deleted from ' . $this->table_name . '.');
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
  
  /**
   * 
   * @param boolean $includeBuffer
   * @return boolean
   */
  protected function readLatLongBoundsLowLevel($includeBuffer = True)
  // --------------------------------------------------------------------------
  {
    # TODO: Replace with newer logic to read from view, see python code for example.
    $ret = False;
    $sql = "SELECT t2.*, t2.max_long_int - min_long_int AS long_diff,
              t2.max_lat_int - min_lat_int AS lat_diff
            FROM
            (
              SELECT ST_X(t1.lower_left_ll) AS min_long, ST_Y(t1.lower_left_ll) AS min_lat,
              	ST_X(t1.upper_right_ll) AS max_long, ST_Y(t1.upper_right_ll) AS max_lat,
              	floor(ST_X(t1.lower_left_ll)) AS min_long_int, floor(ST_Y(t1.lower_left_ll)) AS min_lat_int,
              	ceil(ST_X(t1.upper_right_ll)) AS max_long_int, ceil(ST_Y(t1.upper_right_ll)) AS max_lat_int
              FROM
              (
              	SELECT ST_Transform(ST_GeomFromText('POINT(' || min_x - buffer || ' ' || min_y - buffer || ')', srid_fk), 4326) AS lower_left_ll,
              	  ST_Transform(ST_GeomFromText('POINT(' || max_x + buffer || ' ' || max_y + buffer || ')', srid_fk), 4326) AS upper_right_ll
              	FROM sp.study_area
              	WHERE rowid = $1
              ) t1
            ) t2";
    if (! $includeBuffer)
    {
      $sql = str_replace('buffer', '0', $sql);
    }
    //
    // ----- run the query
    //
    $db = algaeDB::connect();
    if ($db)
    {
      $parms = array($this->rowid);
      $result = pg_query_params($db, $sql, $parms);
      if (! $result)
      {
        algaeDB::errorWithSQL($sql, $parms);
      }
      else
      {
        if (pg_num_rows($result) > 0)
        {
          $row = pg_fetch_array($result);
          $cur = 0;
          $this->min_long = algaeDB::cleanDataRead($row[$cur++]);
          $this->min_lat = algaeDB::cleanDataRead($row[$cur++]);
          $this->max_long = algaeDB::cleanDataRead($row[$cur++]);
          $this->max_lat = algaeDB::cleanDataRead($row[$cur++]);
          $ret = True;
        }
        algaeDB::close($db, $result);
      }
    }
    return $ret;
  }
  
  public function readLatLongBounds($includeBuffer = True)
  // --------------------------------------------------------------------------
  {
    $ret = False;
    if ($this->readLatLongBoundsLowLevel($includeBuffer))
    {
      #
      # ----- make sure the buffer didn't wrap around the earth
      #
      if ( ($this->min_long < $this->max_long) && ($this->min_lat < $this->max_lat) )
      {
        $ret = True;
      }
    }
    if (! $ret)
    {
      #
      # ----- if there was wrap try it without the buffer
      #
      if ($this->readLatLongBoundsLowLevel(False))
      {
        if ( ($this->min_long < $this->max_long) && ($this->min_lat < $this->max_lat) )
        {
          $ret = True;
        }
      }
    }
    return $ret;
  }
  
  /**
   * Read and set the map bounds for the study area.
   * @param object $map Instance on an algaeMap.
   */
  public function setMapBounds($map)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->readLatLongBounds())
    {
      $map->bounds_min_long = $this->min_long;
      $map->bounds_max_long = $this->max_long;
      $map->bounds_min_lat = $this->min_lat;
      $map->bounds_max_lat = $this->max_lat;
      $map->zoomToBounds = True;
    }
    else
    {
      $app->errorMessage('Extents not defined.');
    }
  }
  
}



