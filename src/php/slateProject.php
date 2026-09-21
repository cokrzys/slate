<?php

/**

  slate | Overall project container and support for the sp.project table.
 
  Contains project name, data directory, abbreviation, and other overall project setup items.
  A study area is connected to a project and contains the projection, spatial boundary, and raster setup details.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateProject extends algaeTblNamedObjectBase
{
  
  public $app_user;
  public $folder;
  public $abbreviation;
  public $public;
  public $copyright;
  
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
    $this->table_name = 'sp.project';
    $this->homepage = 'project.php';
    $this->editpage = 'edit_project.php';
    // $this->deletepage = 'delete_project.php';  // TODO: Enable this after making cascade deletes
    $this->abbreviation = null;
    $this->owner = null;
    $this->folder = null;
    $this->public = 'No';
    $this->copyright = null;
    $this->app_user = new algaeTblCoreAppUser();
  }
  
  /**
   * Get links to act on the item.
   * @param boolean $openInNewTab True to open in a new tab, default is False.
   * @return string  HTML string with the links.
   */
  public function getActionLinks($openInNewTab = False)
  // --------------------------------------------------------------------------
  {
    global $app;
    $html = parent::getActionLinks($openInNewTab);
    $html .= $app->config->menu_separator;
    $html .= $app->getPageLink($this->editpage, 'Add a New Project', algaeAccess::ROLE_WRITE, $app->config->app_name, '', $openInNewTab);
    return $html;
  }
  
  public function getItemDirectory($resolution_folder, $item_folder, $rowid)
  // --------------------------------------------------------------------------
  {
    global $app;
    $d = null;
    if ($resolution_folder != null)
    {
      $d = $this->folder . $resolution_folder . '/' . $item_folder;
      $newdir = algaeCore::getPathFromRowid($rowid, $app->config->rowid_directory_levels);
      if (strlen($newdir) > 0)
      {
        $d .= '/' . $newdir . '/';
      }
    }
    else 
    {
      algaeApp::errorMessage('Resolution folder null in ' . __CLASS__ . '.');
    }
    return $d;
  }
  
  protected function postInsert()
  // --------------------------------------------------------------------------
  {
    algaeTblCoreUserParameter::save_parameter(slateApp::CURRENT_PROJECT_ROWID_PARAMETER_NAME, $this->rowid);
    return True;
  }
  
  protected function preInsert()
  // --------------------------------------------------------------------------
  {
    $this->app_user->rowid = algaeTblCoreAppUser::getAppUserRowidForLoggedInUser();
    return $this->setupProjectDirectories();
  }
  
  /**
   * Setup a directory structure for the project.
   * @return boolean True on success, False on fail.
   */
  protected function setupProjectDirectories()
  // --------------------------------------------------------------------------
  {
    global $app;
    $project_directory = algaeCore::getFullPath($app->config->projects_base_folder, $this->folder);
    if (! file_exists($project_directory))
    {
      echo 'Creating directory ', $project_directory, '<p />';
      if (mkdir($project_directory))
      {
        $source_data_directory = algaeCore::getFullPath($project_directory . $app->config->source_data_directory);
        mkdir($source_data_directory);
        mkdir(algaeCore::getFullPath($source_data_directory, $app->config->vector_data_sub_directory));
        mkdir(algaeCore::getFullPath($source_data_directory, $app->config->raster_data_sub_directory));
        mkdir(algaeCore::getFullPath($source_data_directory, $app->config->other_data_sub_directory));
        $app->successMessage('Project directories successfully created.');
        return True;
      }
      else 
      {
        $app->errorMessage('Unable to create ' . $project_directory . '.');
      }
      return False;
    }
    else 
    return True;
  }
  
  /**
   * Show form to edit a record.
   */
  protected function showEntryForm()
  // --------------------------------------------------------------------------
  {
    global $app;
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
    // ----- 
    //
    algaeTable::writeTwoColumns('Name', algaeForm::inputText($this->get_control_id('name'), $this->name, 50, algaeForm::REQUIRED), False);
    algaeTable::writeTwoColumns('Abbreviation', algaeForm::inputText($this->get_control_id('abbreviation'), $this->abbreviation, 10, algaeForm::REQUIRED) .
      $app->getDetailString('Lowercase, no spaces or trailing underscores.  Default for filename prefixes.'), False);
    algaeTable::writeTwoColumns('Folder', algaeForm::inputText($this->get_control_id('folder'), $this->folder, 20, algaeForm::REQUIRED) . 
      $app->getDetailString('Lowercase, no spaces.'), False);
    algaeTable::writeTwoColumns('Copyright', algaeForm::inputText($this->get_control_id('copyright'), $this->copyright, 50), False);
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
    algaeTable::writeTwoColumns('Status', $this->record_status->getControl($this), False);
    algaeTable::end();
    echo '<p />';
    $f->endForm('Save', False);
    echo '<p /><br />';
  }
  
  /**
   * Show form to edit a record.
   */
  public function showForm()
  // --------------------------------------------------------------------------
  {
    $this->showEntryForm();
    /* TODO: Obsolete ?
    if ($this->added)
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
    */
  }
  
  /**
   * 
   * {@inheritDoc}
   * @see algaeTblBase::reportRecords()
   */
  public function reportRecords($tableId = 'slateProjectsTable', $whereClause = '', $maxRecords = 10000)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->numVariableErrors() == 0)
    {
      $sql = $this->get_sql();  // TODO: Add filter for projects for a user only
      $sql .= $whereClause;
      $add_link = $app->getPageLink($this->editpage, 'Add a Project', algaeAccess::ROLE_WRITE, $app->settings->appName, '') . '<p />';
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
              array('Rowid', '10%'),
              array('Name', '25%'),
              array('Description', '45%')
            );
            algaeTable::writeHeader($header_array, True);
            //
            // ----- loop through the results
            //
            $p = new slateProject();
            while ($row = pg_fetch_array($result))
            {
              $p->init();
              $p->read_row_from_database($row);
              echo '<tr>';
              algaeTable::writeData($p->getActionLinks(), False);
              algaeTable::writeData($p->rowid);
              algaeTable::writeData($p->getHomepageLink(), False);
              algaeTable::writeData($p->description);
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
  
  protected function reportOverallDetails()
  // --------------------------------------------------------------------------
  {
    echo $this->getActionLinks(), '<p />';
    algaeTable::start('projectDetailsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Project', '<b>' . $this->name . '</b>', False);
    algaeTable::writeTwoColumns('Owner', $this->app_user->username);
    algaeTable::writeTwoColumns('Public', $this->public);
    algaeTable::writeTwoColumns('Abbreviation', $this->abbreviation);
    algaeTable::writeTwoColumns('Folder', $this->folder);
    algaeTable::writeTwoColumns('Copyright', $this->copyright);
    algaeTable::writeTwoColumns('Description', $this->description);
    algaeTable::writeTwoColumns('Created', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
  }
  
  protected function reportDetails()
  // --------------------------------------------------------------------------
  {
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#overview_tab', 'Overview'),
      // array('#shapefiles_tab', 'Raw Data'),
      array('#study_area_tab', 'Study Area'),
      array('#bounds_tab', 'Lat-Long Bounds'),
      array('#file_size_estimates', 'File Size Estimates')
      // array('#map_tab', 'Map'),
      // array('#build_tab', 'Processing'),
      // array('#layers_tab', 'Layers')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- study_area_tab
    //
    echo '<div id="study_area_tab">';
    /*
    $sa = new slateStudyArea();
    $sa->reportDetailsForProject($this->rowid);
    */
    echo '</div>';
    //
    // ----- file_size_estimates
    //
    echo '<div id="file_size_estimates">';
    // refResolution::reportFileSizeEstimates($sa);
    echo '</div>';
    //
    // ----- bounds_tab
    //
    echo '<div id="bounds_tab">';
    // $sa->readLatLongBounds();
    // $sa->reportLatLongBounds();
    echo '</div>';
    //
    // ----- map tab
    //
    // echo '<div id="map_tab">';
    // echo '</div>';
    //
    // ----- shapefiles tab
    //
//     echo '<div id="shapefiles_tab">';
//     $s = new slateShapefile();
//     $s->project->rowid = $this->rowid;
//     $s->reportRecords('slateShapefilesTable', ' WHERE sp.project.rowid = ' . $this->rowid);
//     echo '</div>';
    //
    // ----- processing tab
    //
//     echo '<div id="build_tab">';
//     $ps = new slateGeoprocess();
//     $ps->reportForProject($this->rowid);
//     echo '</div>';
    //
    // ----- layers tab
    //
//     echo '<div id="layers_tab">';
//     $l = new slateLayer();
//     $l->reportForProject($this->rowid);
//     echo '</div>';
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  public function reportProjects()
  // --------------------------------------------------------------------------
  {
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#projects_tab', 'Projects')
    ));
    //
    // ----- projects_tab
    //
    echo '<div id="projects_tab">';
    $this->reportRecords();
    echo '</div>';
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  public function showWhatsBeingDeleted()
  // --------------------------------------------------------------------------
  {
    echo 'Deleting project <b>', $this->name, '</b>.</p>';
    // TODO: Add the cascades down.
  }
  
  public function delete()
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_errors = 0;
    if (algaeDB::deleteFromTable($this->table_name, 'rowid', $this->rowid))
    {
      $app->successMessage('Record deleted from ' . $this->table_name . '.');
    }
    else
    {
      $num_errors += 1;
    }
    if ($num_errors == 0) $this->deleted = True;
    return $num_errors;
  }
  
  public function readDetailsForProjectRowidOnTheURL()
  // --------------------------------------------------------------------------
  {
    global $app;
    if (isset($_REQUEST['project_rowid_fk']))
    {
      $this->read_row_from_database_with_rowid($_REQUEST['project_rowid_fk']);
      if (strlen($this->name) > 0)
      {
        return True;
      }
      else
      {
        $app->errorMessage('Problem reading details for project with rowid ' . $_REQUEST['project_rowid_fk'] . '.');
      }
    }
    else
    {
      $app->errorMessage('Project rowid not found on the URL.');
    }
    return False;
  }
  
  public function showHomepage()
  // --------------------------------------------------------------------------
  {
    global $app;
    $this->rowid = $app->getCurrentProjectRowid(False);
    if ($this->rowid > 0)
    {
      $this->read_row_from_database_with_rowid($this->rowid);
      $this->reportDetails();
    }
    else 
    {
      header("Location: {$this->editpage}");
    }
  }
  
  /**
   * Get a default prefix, typically to start an output file.
   * @param string $prefix Existing prefix, return it if defined.
   * @return string New default prefix or the one passed-in if it exists.
   */
  public function getDefaultPrefix($prefix = null)
  // --------------------------------------------------------------------------
  {
    if (strlen($prefix) == 0) return $this->abbreviation . '_';
    return $prefix;
  }
  
  /**
   * Get the study area for the project.
   * @return slateStudyArea
   */
  public static function getCurrentProjectStudyArea()
  // --------------------------------------------------------------------------
  {
    return null;
    /**
    global $app;
    $sa = new slateStudyArea();
    $sa->readRowFromDatabaseWithProjectRowid($app->getCurrentProjectRowid());
    return $sa;
    */
  }
  
}



