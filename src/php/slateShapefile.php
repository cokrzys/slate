<?php

/**

  slate | Shapefile and sp.shapefile support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateShapefile extends algaeTblNamedObjectBase
{
  
  public $srid_fk;
  public $project;
  public $geometry_type;
  public $data_group;
  public $source_filename;
  public $source_url;
  public $style;
  public $coord_system_name;
  public $proj_min_x;
  public $proj_max_x;
  public $proj_min_y;
  public $proj_max_y;
  public $ll_min_x;
  public $ll_min_y;
  public $ll_max_x;
  public $ll_max_y;
  public $selectpage;
  
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
    $this->table_name = 'sp.shapefile';
    $this->homepage = 'shapefile.php';
    $this->editpage = 'edit_shapefile.php';
    $this->deletepage = 'delete_shapefile.php';
    $this->selectpage = 'select_shapefile.php';
    $this->srid_fk = null;
    $this->project = new slateProject();
    $this->geometry_type = new refGeometryType();
    $this->data_group = new refDataGroup();
    $this->source_filename = null;
    $this->source_url = null;
    $this->style = null;
    $this->record_status->name = 'Active';
    $this->coord_system_name = null;
    $this->proj_min_x = null;
    $this->proj_max_x = null;
    $this->proj_min_y = null;
    $this->proj_max_y = null;
    $this->ll_min_x = null;
    $this->ll_min_y = null;
    $this->ll_max_x = null;
    $this->ll_max_y = null;
    $this->debug = true;
  }
  
  /**
   * Read the shapefile extents in lat-long WGS84 coordinates.
   */
  protected function readLatLongExtents()
  // --------------------------------------------------------------------------
  {
    $ret = False;
    $sql = "SELECT
              ST_XMin(extent::geometry) AS xmin,
              ST_YMin(extent::geometry) AS ymin,
              ST_XMax(extent::geometry) AS xmax,
              ST_YMax(extent::geometry) AS ymax
              FROM $this->table_name
              WHERE rowid = $1";
    //
    // ----- connect to the database and read the data
    //
    $db = algaeDB::connect();
    if ($db)
    {
      $result = pg_query_params($db, $sql, array($this->rowid));
      if (! $result)
      {
        algaeDB::errorWithSQL($sql);
      }
      if (pg_num_rows($result) > 0)
      {
        while ($row = pg_fetch_array($result))
        {
          $cur = 0;
          $this->ll_min_x = algaeDB::cleanDataRead($row[$cur++]);
          $this->ll_min_y = algaeDB::cleanDataRead($row[$cur++]);
          $this->ll_max_x = algaeDB::cleanDataRead($row[$cur++]);
          $this->ll_max_y = algaeDB::cleanDataRead($row[$cur++]);
        }
        if ( ($this->ll_min_x >= -180) && ($this->ll_min_x <= 180)) $ret = True;
      }
      pg_free_result($result);
      algaeDB::close($db);
    }
    return $ret;
  }
  
  /**
   * Show form to edit a record.
   */
  protected function showEntryForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#overview_tab', 'Overview'),
      array('#fields_tab', 'Details') 
    ));
    echo '<div id="overview_tab">';
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
    //
    //
    //
    echo '<input type="hidden" name="attribute_processor" value="True" />';
    echo '<input type="hidden" name="project_rowid_fk" value="', $this->project->rowid, '" />';
    echo '<input type="hidden" name="project_name" value="', $this->project->name, '" />';
    echo '<input type="hidden" name="source_filename" value="', $this->source_filename, '" />';
    echo '<input type="hidden" name="proj_min_x" value="', algaeCore::getFormattedNumber($this->proj_min_x, 2, -1.0e30, ''), '" />';
    echo '<input type="hidden" name="proj_max_x" value="', algaeCore::getFormattedNumber($this->proj_max_x, 2, -1.0e30, ''), '" />';
    echo '<input type="hidden" name="proj_min_y" value="', algaeCore::getFormattedNumber($this->proj_min_y, 2, -1.0e30, ''), '" />';
    echo '<input type="hidden" name="proj_max_y" value="', algaeCore::getFormattedNumber($this->proj_max_y, 2, -1.0e30, ''), '" />';
    //
    //
    //
    if ($this->rowid > 0)
    {
      echo '<input type="hidden" name="rowid" value="', $this->rowid, '" />';
    }
    //
    // ----- table to keep items aligned
    //
    algaeTable::start('formTable', 'algae_form_table', '');
    algaeTable::writeHeader(array(), False);
    //
    // ----- name
    //
    algaeTable::writeTwoColumns('Name', algaeForm::inputText('name', $this->name, 50, algaeForm::REQUIRED), False);
    //
    // ----- coordinate system
    //
    algaeTable::writeTwoColumns('Coordinate System EPSG Code (1)',
      algaeForm::inputText('srid_fk', $this->srid_fk, 10, algaeForm::REQUIRED) . '&nbsp;&nbsp;' .
      $app->getPageLink('browse_coordinate_systems.php', 'Coordinate Systems', algaeAccess::ROLE_READ, $app->config->app_name, '', True),
      False);
    //
    // ----- geometry type
    //
    algaeTable::writeTwoColumns('Geometry Type', algaeForm::selectWithTableAndField('ref.geometry_type', 'name', 
      $this->get_control_id('geometry_type_rowid_fk'), $this->geometry_type->name, algaeForm::REQUIRED), False);
    //
    // ----- source url
    //
    algaeTable::writeTwoColumns('Source Link', algaeForm::inputText('source_url', $this->source_url, 100), False);
    //
    // ----- description
    //
    algaeTable::writeTwoColumns('Description', '', False);
    echo '<tr><td colspan="2">';
    echo '<textarea name="description" cols="83" rows="7">', algaeCore::toHtml($this->description), '</textarea><p />';
    echo '</td></tr>';
    //
    // ----- record status
    //
    algaeTable::writeTwoColumns('Status', algaeForm::selectWithTableAndField('ref.record_status', 'name',
      $this->get_control_id('record_status_rowid_fk'), $this->record_status->name), False);
    algaeTable::end();
    echo '<p />';
    $f->endForm('Save', False);
    echo '(1) The coordinate system the data in the shapefile is in.<p />';
    echo '</div>';
    // echo '<p /><br />';
    //
    // ----- fields tab
    //
    echo '<div id="fields_tab">';
    $this->reportFields();
    echo '</div>';
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  /**
   * Show form to edit a record.
   */
  public function showForm()
  // --------------------------------------------------------------------------
  {
    if ($this->inserted)
    {
      echo 'Goto the ', $this->getHomepageLink(), ' homepage.<p />';
    }
    elseif ($this->updated)
    {
      header("Location: {$this->homepage}?rowid={$this->rowid}");
    }
    else 
    {
      $this->showEntryForm();
    }
  }
  
  /**
   * 
   * {@inheritDoc}
   * @see algaeTblBase::reportRecords()
   */
  public function reportRecords($tableId = 'slateShapefilesTable', $whereClause = '', $maxRecords = 10000)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->numVariableErrors() == 0)
    {
      $sql = $this->get_sql();  // TODO: Add filter for projects for a user only
      $sql .= $whereClause;
      $add_link = $app->getPageLink($this->selectpage, 
        'Add a Shapefile', algaeAccess::ROLE_WRITE, $app->config->app_name, '') . '<p />';
      //
      // ----- run the query
      //
      $db = algaeDB::connect();
      if ($db)
      {
        $result = pg_query($db, $sql);
        if (! $result)
        {
          algaeDB::errorWithSQL($sql);
        }
        else
        {
          //
          // ----- display the result rows if there are any
          //
          $num_rows = pg_num_rows($result);
          if ($num_rows > 0)
          {
            echo $add_link;
            //
            // ----- initial the table
            //
            $tableId = $this->getDefaultRecordsTableId($tableId);
            algaeTable::initTablesorterJavascript($tableId, '[[2,0]]');
            algaeTable::start($tableId, 'tablesorter', 'width:100%;');
            //
            // ----- table header
            //
            $header_array = array(
              array('Action', '10%'),
              array('Name', '25%'),
              array('Geometry Type', '15%'),
              array('Coordinate System', '20%'),
              array('Description', '30%')
            );
            algaeTable::writeHeader($header_array, True);
            //
            // ----- loop through the results
            //
            $s = new slateShapefile();
            while ($row = pg_fetch_array($result))
            {
              $s->init();
              $s->read_row_from_database($row);
              $s->read_row_from_database_with_rowid($s->rowid, True);
              echo '<tr>';
              algaeTable::writeData($s->getActionLinks(), False);
              algaeTable::writeData($s->getHomepageLink(), False);
              algaeTable::writeData($s->geometry_type->name);
              algaeTable::writeData($s->coord_system_name);
              algaeTable::writeData(algaeCore::getStringWithLinks($s->description), False);
              echo '</tr>';
            }
            algaeTable::end();
          }
          else
          {
            echo $add_link;
          }
          algaeDB::close($db, $result);
        }
      }
    }
  }
  
  /**
   * 
   * @return boolean
   */
  protected function buildDownloadFile()
  // --------------------------------------------------------------------------
  {
    $compress_files = algaeFile::getWildcardSpec($this->source_filename);
    $compressed_file = algaeFile::removeExtension($this->source_filename) . '.zip';
    $cmd = "zip -j " . $compressed_file . " " . $compress_files;
    exec($cmd);
    if (file_exists($compressed_file)) return True;
    return False;
  }
  
  /**
   * 
   */
  protected function reportOverallDetails()
  // --------------------------------------------------------------------------
  {
    global $app;
    echo $this->getActionLinks(), '<p />';
    algaeTable::start('projectDetailsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Name', '<b>' . $this->name . '</b>', False);
    algaeTable::writeTwoColumns('Project', $this->project->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Owner', $this->user->username);
    algaeTable::writeTwoColumns('EPSG Code', $this->srid_fk . '&nbsp;&nbsp;' . 
        $app->getPageLink('browse_coordinate_systems.php', 'Coordinate Systems', algaeAccess::ROLE_READ, $app->config->app_name, '', True), False);
    algaeTable::writeTwoColumns('Coordinate System', $this->coord_system_name);
    algaeTable::writeTwoColumns('Geometry Type', $this->geometry_type->name);
    $this->buildDownloadFile();
    algaeTable::writeTwoColumns('Filename', $this->source_filename . '&nbsp;&nbsp;' .
      algaeFile::getDownloadLink(algaeFile::removeExtension($this->source_filename) . '.zip'), False);
    if (strlen($this->source_url) > 0)
    {
      algaeTable::writeTwoColumns('Source Link', '<a href="' . $this->source_url . '" target="_blank">' . $this->source_url . '</a>', False);
    }
    else 
    {
      algaeTable::writeTwoColumns('Source Link', '');
    }
    // algaeTable::writeTwoColumns('Style', $this->style);
    algaeTable::writeTwoColumns('Description', algaeCore::getStringWithLinks($this->description), False);
    algaeTable::writeTwoColumns('Uploaded', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
  }
  
  /**
   * Report fields and other shapefile details.
   * Example ogrinfo call:
   * ogrinfo -fields=YES GGDD_Limited_Fields.shp GGDD_Limited_Fields -summary
   */
  public function reportFields()
  // --------------------------------------------------------------------------
  {
    $outputFile = $this->source_filename . '.ogrinfo';
    $basename = basename($this->source_filename, '.shp');
    $cmd = 'ogrinfo -fields=YES ' . $this->source_filename . ' ' . $basename . ' -summary > ' . $outputFile;
    exec($cmd);
    # echo $cmd, '<p />';
    algaeFile::showTextFile($outputFile, 'Metadata from ogrinfo:');
  }
  
  /**
   * Get the name of the most recently loaded shapefile for a project and geometry type.
   * @param integer $project_rowid_fk The project rowid.
   * @param string $geom_type The geometry type, i.e. 3DPolylines, 3DPolygon, or 3DPoint.
   */
  public function getMostRecent($project_rowid_fk, $geom_type)
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT name
            FROM sp.shapefile
            WHERE project_rowid_fk = $1
              AND geometry_type_rowid_fk = (SELECT rowid FROM ref.geometry_type WHERE name = $2)
            ORDER BY rowid DESC
            LIMIT 1";
    return algaeDB::getScalarString($sql, array($project_rowid_fk, $geom_type));
  }
  
  protected function getBestRes($xsize, $ysize, $target_size)
  // --------------------------------------------------------------------------
  {
    $res_options = array(1,5,10,25,50,100,250,500,1000,5000,10000,50000,100000,500000,1000000,5000000,10000000,50000000,100000000);
    $num_bytes = 2;
    $closest_diff = 1.0e30;
    $best_res = null;
    foreach ($res_options as $test)
    {
      $size = ($xsize / $test) * ($ysize / $test) * $num_bytes;
      // DO NOT re-enable this or the AJAX call will fail 
      // echo 'size at ', $test, ' = ', $size, '<p />';
      $diff = abs($target_size - $size);
      if ($diff < $closest_diff)
      {
        $closest_diff = $diff;
        $best_res = $test;
      }
    }
    return $best_res;
  }
  
  public function setupDefaults($showCalcs = True)
  // --------------------------------------------------------------------------
  {
    $this->setExtentsFromShapefile();
    if ($showCalcs)
    {
      echo 'x min, max = ', $this->proj_min_x, ', ', $this->proj_max_x, '<p />';
      echo 'y min, max = ', $this->proj_min_y, ', ', $this->proj_max_y, '<p />';
    }
    $xsize = $this->proj_max_x - $this->proj_min_x;
    $ysize = $this->proj_max_y - $this->proj_min_y;
    if ($showCalcs) echo 'xsize, ysize = ', $xsize, ', ', $ysize, '<p />';
    //
    //
    //

    $target_size = 20000000;
    $best_high_res = $this->getBestRes($xsize, $ysize, $target_size);
    $best_medium_res = $this->getBestRes($xsize, $ysize, $target_size / 5);
    $best_low_res = $this->getBestRes($xsize, $ysize, ($target_size / 5) / 5);
    
    if ($showCalcs)
    {
      echo 'best high res = ', $best_high_res, '<p />';
      echo 'best medium res = ', $best_medium_res, '<p />';
      echo 'best low res = ', $best_low_res, '<p />';
    }
    
    $min_x = floor($this->proj_min_x / $best_low_res) * $best_low_res;
    $max_x = ceil($this->proj_max_x / $best_low_res) * $best_low_res;
    $min_y = floor($this->proj_min_y / $best_low_res) * $best_low_res;
    $max_y = ceil($this->proj_max_y / $best_low_res) * $best_low_res;
    if ($showCalcs)
    {
      echo 'x min, max = ', $min_x, ', ', $max_x, '<p />';
      echo 'y min, max = ', $min_y, ', ', $max_y, '<p />';
    }
    
    return array($min_x, $max_x, $min_y, $max_y, $best_high_res, $best_medium_res, $best_low_res);
  }
  
  protected function reportDetails()
  // --------------------------------------------------------------------------
  {
    global $app;
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#overview_tab', 'Overview'),
      array('#map_tab', 'Map'),
      array('#fields_tab', 'Details')
    ));
    //
    // ----- overview tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- map tab
    //
    echo '<div id="map_tab">';
    if ($this->readLatLongExtents())
    {
      $m = new algaeMap();
      $m->bounds_min_long = $this->ll_min_x;
      $m->bounds_max_long = $this->ll_max_x;
      $m->bounds_min_lat = $this->ll_min_y;
      $m->bounds_max_lat = $this->ll_max_y;
      $m->zoomToBounds = True;
      // echo 'DEBUG: ', $m->bounds_min_long, ', ', $m->bounds_max_long, '<p />';
      $m->startMapFile();
      $m->addShapefileLayer($this->source_filename, $this->geometry_type->name, 'shapefile', $this->name, 1, $this->srid_fk);
      $m->endMapFile();
      $m->show();
    }
    else
    {
      $app->errorMessage('Extents not defined.');
    }
    echo '</div>';
    //
    // ----- fields tab
    //
    echo '<div id="fields_tab">';
    $this->reportFields();
    echo '</div>';
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  public function selectShapefileForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    algaeForm::startSingleTab('Shapefile Components');
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)      
    {
      $this->project->read_row_from_database_with_rowid($this->project->rowid);
      $f = new algaeForm();
      echo 'Shapefile selection for project <b>', $this->project->name, '</b></p>';
      echo '<form action="upload_shapefile.php?project_rowid_fk=' . $this->project->rowid . '" method="post" enctype="multipart/form-data">';
      echo '<input type="hidden" name="file_selector" value="True" />';
      echo 'Components:&nbsp;&nbsp;';
      echo '<input class="ui-button ui-widget ui-corner-all" name="components[]" type="file" multiple="" /><p />';
      $f->submitButton('Upload');
      echo '</form>';
      echo 'A shapefile consists of at least three mandatory pieces with extensions .shp, .shx, and .dbf.<p />';
      echo 'Projection information is stored in an optional but typically required file with a .prj extension.<p />';
      echo 'To upload a shapefile select all of the component files.<p />';
      # echo "It's ok to select and upload optional files.<p />";
      echo 'For more information see the <a target="_blank" href="https://en.wikipedia.org/wiki/Shapefile">Wikipedia Article on Shapefiles</a>.</p>';
    }
    algaeForm::endSingleTab();
  }
  
  public function setExtentsFromShapefile()
  // --------------------------------------------------------------------------
  {
    global $app;
    $output = array();
    exec($app->scriptsFolder . 'shp_extents.sh ' . $this->source_filename, $output);
    foreach ($output as $key => $line)
    {
      if ($key == 0)
      {
        $pieces = explode(" ", $line);
        if (count($pieces) == 4)
        {
          $this->proj_min_x = floatval($pieces[0]);
          $this->proj_max_x = floatval($pieces[1]);
          $this->proj_min_y = floatval($pieces[2]);
          $this->proj_max_y = floatval($pieces[3]);
        }
      }
    }
  }
  
  /**
   * Set defaults for a newly uploaded shapefile.
   * @param array $files_array Are of individual shapefile components.
   */
  public function setDefaults($files_array)
  // --------------------------------------------------------------------------
  {
    global $app;
    foreach ($files_array as $file)
    {
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if (strcasecmp($ext, 'shp') === 0)
      {
        $this->source_filename = $file;
        $this->name = pathinfo($file, PATHINFO_FILENAME);
        $this->setExtentsFromShapefile();
        //
        // ----- geometry type
        //
        $output = array();
        exec($app->scriptsFolder . 'shp_geometry_type.sh ' . $this->source_filename, $output);
        foreach ($output as $key => $line)
        {
          if ($key == 1)
          {
            if (strcasecmp($line, 'Polygon') == 0) 
            {
              $this->geometry_type->name = '3DPolygon';
            }
            else
            {
              $app->errorMessage('Unrecognized geometry type ' . $line . '.');
            }
          }
        }
      }
    }
  }
  
  public function showUploadProcessor()
  // --------------------------------------------------------------------------
  {
    if ($this->project->readDetailsForProjectRowidOnTheURL())
    {
      if (isset($_POST["submit"]))
      {
        //
        // ----- if uploading show form to add database entries
        //
        if (isset($_POST['file_selector']))
        {
          $u = new algaeFile();
          // TODO: Better way other than just adding a backslash.
          $u->target_dir = $this->project->getDirectory(slateProject::VECTOR_DATA_DIRECTORY) . '/';
          if ($u->uploadMultiple('components') > 0)
          {
            /*
            $sa = $this->project->getCurrentProjectStudyArea();
            if ($sa != null)
            {
              $this->srid_fk = $sa->srid_fk;
            }
            $this->setDefaults($u->uploaded_array);
            */
            if (strlen($this->source_filename) > 0)
            {
              $this->showForm();
            }
          }
        }
        else
        {
          //
          // ----- process form with database entries
          //
          if (isset($_POST['attribute_processor']))
          {
            $this->processForm();
            $this->showForm();
          }
        }
      }
    }
  }
  
  /**
   * Show what's being deleted.
   * {@inheritDoc}
   * @see algaeTblBase::showWhatsBeingDeleted()
   */
  public function showWhatsBeingDeleted()
  // --------------------------------------------------------------------------
  {
    echo 'Deleting shapefile <b>', $this->name, '</b>';
    echo ' from project ', $this->project->getHomepageLink(), '.<p />';
    
    $study_area_rowid_fk = algaeDB::getScalarInteger("SELECT rowid FROM sp.study_area WHERE shapefile_rowid_fk = $1", array($this->rowid), 0);
    if ($study_area_rowid_fk > 0)
    {
      echo 'The study area referencing this shapefile will be deleted.<p />';
      $sa = new slateStudyArea();
      $sa->read_row_from_database_with_rowid($study_area_rowid_fk);
      $sa->showWhatsBeingDeleted();
    }
  }
  
  /**
   * Delete the shapefile.
   * {@inheritDoc}
   * @see algaeTblBase::delete()
   */
  public function delete()
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_errors = 0;
    //
    // ----- delete the study area and everything below it as needed
    //
    $sa = new slateStudyArea();
    $study_area_rowid_fk = algaeDB::getScalarInteger("SELECT rowid FROM $sa->table_name WHERE shapefile_rowid_fk = $1", array($this->rowid), 0);
    if ($study_area_rowid_fk > 0)
    {
      $sa->read_row_from_database_with_rowid($study_area_rowid_fk);
      $num_errors += $sa->delete();
    }
    //
    // ----- delete files associated with the shapefile
    //
    if ($num_errors == 0)
    { 
      $searchPath = algaeFile::getWildcardSpec($this->source_filename); 
      foreach (glob($searchPath) as $filename)
      {
        if (! algaeFile::deleteFileFromFilesystem($filename)) $num_errors++;
      }
    }
    //
    // ----- delete shapefile from the database
    //
    if ($num_errors == 0)
    {
      if (algaeDB::deleteFromTable($this->table_name, 'rowid', $this->rowid))
      {
        $app->successMessage('Shapefile record deleted from ' . $this->table_name . '.');
        echo 'Goto the ', $this->project->getHomepageLink(), ' homepage.<p />';
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
   * Report records for the current project.
   */
  public function reportRecordsForCurrentProject()
  // --------------------------------------------------------------------------
  {
    global $app;
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)
    {
      $this->reportRecords('slateShapefilesTable', " WHERE $this->table_name.project_rowid_fk = " . $this->project->rowid);
    }
  }
  
}




