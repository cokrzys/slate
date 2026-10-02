<?php

/**

  slate | A GeoProcess and support for the sp.geoprocess table.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcess extends algaeTblBase
{
  
  public $project;
  public $process;
  public $name;
  public $mainTabName;
  public $php_class;
  public $command;
  public $parameters;
  public $batch_parameters;
  public $description;
  public $num_dependencies;
  public $data_type;
  public $set_vars_script;
  public $select_geoprocess_page;
  public $available_geoprocesses;
  public $data_group;
  public $data_distribution;
  public $units;
  public $num_decimals;
  public $showBatchParametersTab;
  public $showAddLink;
  public $sequence;
  
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
    global $app;
    parent::init();
    $this->itemName = 'GeoProcess';
    $this->itemNamePlural = 'GeoProcesses';
    $this->table_name = 'sp.geoprocess';
    $this->mainTabName = 'TODO: Set mainTabName in derived class.';
    $this->homepage = 'geoprocess.php';
    $this->editpage = 'edit_geoprocess.php';
    $this->deletepage = 'delete_geoprocess.php';
    $this->select_geoprocess_page = 'select_geoprocess.php';
    $this->browsepage = 'browse_geoprocesses.php';
    $this->showBatchParametersTab = False;
    $this->showAddLink = True;
    $this->project = new slateProject();
    $this->process = new algaeTblCoreProcess();
    $this->data_type = new refDataType();
    $this->data_group = new refDataGroup();
    $this->data_distribution = new refDataDistribution();
    $this->units = new refUnits();
    $this->data_type->name = 'Float32';
    $this->name = '';
    $this->php_class = get_class($this);
    $this->command = '';
    $this->parameters = '';
    $this->description = '';
    $this->batch_parameters = '';
    $this->num_dependencies = 0;
    $this->num_decimals = 2;
    $this->sequence = 100;
    $this->set_vars_script = algaeCore::getFullPath($app->config->getAppConfigParameter($app->config->app_name, 'scriptsPath'), 'set_common_vars.sh');
    $this->available_geoprocesses = array();
    $this->available_geoprocesses[] = array('Categorical Layer', 'edit_categorical.php');
    $this->available_geoprocesses[] = array('Create a Mask', 'edit_mask.php');
    $this->available_geoprocesses[] = array('Proximity Raster', 'edit_proximity.php');
    $this->available_geoprocesses[] = array('Import Raster', 'edit_reproject_raster.php');
    // $this->available_geoprocesses[] = array('Re-Project Shapefile', 'edit_reproject_shapefile.php');
    // $this->available_geoprocesses[] = array('Similarity Model', 'edit_similarity_model.php');
    $this->available_geoprocesses[] = array('Subset Shapefile', 'edit_subset_shapefile.php');
    $this->available_geoprocesses[] = array('Overlay Raster', 'edit_overlay.php');
  }
  
  /**
   * 
   * @return NULL|string
   */
  public function getDirectory($resolution_folder = null)
  # ---------------------------------------------------------------------------
  {
    global $app;
    if ($resolution_folder == null)
    {
      $resolution_folder = $app->getResolutionFolder();
    }
    return $this->project->getItemDirectory($resolution_folder, $app->config->geoprocesses_folder, $this->rowid);
  }
  
  /**
   * SHOULD BE ALL HANDLED IN BASE CLASS.
   * Get a link to the homepage for a record.
   * @param string $label Label for the link, will be the name if not specified.
   * @param integer $role Role constant, algaeAccess::ROLE_READ if not defined.
   * @param boolean $new_page True to open in a new tab, default is False.
   * @return string The link.
   */
  public function getHomepageLinkObsolete($label = null, $role = algaeAccess::ROLE_READ, $new_page = False, $title = null)
  // --------------------------------------------------------------------------
  {
    if ($this->homepage != null)
    {
      global $app;
      if ($label == null) $label = $this->name;
      return $app->getPageLink($this->homepage . '?rowid=' . $this->rowid, $label, $role, $app->settings->appName, '', $new_page);
    }
    return '';
  }
  
  /**
   * Decode parameters stored in $this->parameters.  Implemented in a derived
   * class if needed.
   */
  protected function decodeParameters()
  // --------------------------------------------------------------------------
  {
  }
  
  public function getRunLink($addMenuSeparator = True)
  // --------------------------------------------------------------------------
  {
    global $app;
    $html = '';
    if ($addMenuSeparator) { $html .= $app->settings->menuSeparator; }
    $html .= $app->getPageLink('run_geoprocess.php?rowid=' . $this->rowid, 'Run', algaeAccess::ROLE_WRITE, $app->settings->appName, '');
    return $html;
  }
  
  /**
   * Get links to act on the item.
   * @return string  HTML string with the links.
   */
  public function getActionLinks($openInNewTab=False)
  // --------------------------------------------------------------------------
  {
    $html = parent::getActionLinks();
    $html .= $this->getRunLink();
    return $html;
  }
  
  /**
   * Update the process rowid.
   * @return boolean
   */
  protected function updateProcessRowid()
  // --------------------------------------------------------------------------
  {
    if ( ($this->process->rowid > 0) && ($this->rowid > 0) )
    {
      $sql = "UPDATE $this->table_name SET process_rowid_fk = $1 WHERE rowid = $2";
      return algaeDB::executeQuery($sql, array($this->process->rowid, $this->rowid));
    }
    return False;
  }
  
  /**
   * Report overall details for the object.
   */
  protected function reportOverallDetails()
  // --------------------------------------------------------------------------
  {
    global $app;
    echo $this->getActionLinks(), '<p />';
    algaeTable::start('geoprocessDetailsTable', 'algae_table', 'width:60%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Name', '<b>' . $this->name . '</b>', False);
    algaeTable::writeTwoColumns('Data Group', algaeCore::getColorBlock($this->data_group->html_color, True, $this->data_group->name), False);
    algaeTable::writeTwoColumns('Data Distribution', algaeCore::getColorBlock($this->data_distribution->html_color, True, $this->data_distribution->name), False);
    algaeTable::writeTwoColumns('Data Type', $this->data_type->name);
    
    $units = '-';
    if ( ($this->units->name != null) && (strlen($this->units->name) > 0) )
    {
      $units = $this->units->name . ' (' . $this->units->abbreviation . ')';
    }
    algaeTable::writeTwoColumns('Units', $units, False);
    $decStr = strval($this->num_decimals);
    if (strlen($decStr) == 0) { $decStr = '0'; }
    algaeTable::writeTwoColumns('Num Decimals', $decStr, False);
    algaeTable::writeTwoColumns('Command', $this->command, False);
    algaeTable::writeTwoColumns('Project', $this->project->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Status', $this->process->getStatusMessage(), False);
    algaeTable::writeTwoColumns('Process', $this->process->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Sequence', $this->sequence, False);
    // algaeTable::writeTwoColumns('Results', $this->process->getResultsLink(), False);
    // algaeTable::writeTwoColumns('Parameters', $this->parameters);
    algaeTable::writeTwoColumns('Folder', $this->getDirectory());
    algaeTable::writeTwoColumns('Notes', $this->description);
    algaeTable::writeTwoColumns('PHP Class', $this->php_class, False);
    // algaeTable::writeTwoColumns('Data Type', $this->data_type->name);
    algaeTable::writeTwoColumns('Created', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
  }
  
  protected function isTextFile($filename)
  // --------------------------------------------------------------------------
  {
    $text_extensions = array('log', 'json', 'xml', 'leg', 'prj');
    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
    foreach ($text_extensions as $ext)
    {
      if (strcasecmp($file_extension, $ext) == 0)
      {
        return True;
      }
    }
    return False;
  }
  
  /**
   * Get menu options for a file.  Actions depend on the file type.
   * @param string $filename Filename to get actions for.
   */
  protected function getFileActions($filename)
  // --------------------------------------------------------------------------
  {
    global $app;
    $html = '';
    $html = algaeFile::getDownloadLink($filename, 'Download', False);
    if ($this->isTextFile($filename))
    {
      $html .= $app->settings->menuSeparator;
      $html .= $app->getPageLink('view_text_file.php?filename=' . urlencode($filename), 'View', algaeAccess::ROLE_READ, $app->settings->appName, '', True);
    }
    return $html;
  }
  
  /**
   * Report files for the process.
   */
  protected function reportFiles()
  // --------------------------------------------------------------------------
  {
    $folder = $this->getDirectory();
    if (is_dir($folder) === true)
    {
      $files = scandir($folder);
      echo sizeof($files), ' file(s) in ', $folder, '<p />';
      //
      // ----- initial the table
      //
      $tableId = 'filesTable';
      algaeTable::initTablesorterJavascript($tableId, '[[0,0]]', True, "headers: {2: {sorter:'milDate'} }");
      algaeTable::start($tableId, 'tablesorter', 'width:80%;');
      //
      // ----- table header
      //
      $header_array = array(
        array('Filename', '40%'),
        array('Size (bytes)', '20%'),
        array('Date', '20%'),
        array('Actions', '20%')
      );
      algaeTable::writeHeader($header_array, True);
      //
      // ----- loop through the results
      //
      foreach ($files as $file)
      {
        if ( ($file != '.') && ($file != '..') )
        {
          $full_filename = $this->getDirectory() . $file;
          echo '<tr>';
          algaeTable::writeData($file);
          algaeTable::writeData(algaeCore::getFormattedNumber(filesize($full_filename), 0));
          algaeTable::writeData(date("d-M-Y G:i:s", filemtime($full_filename)), 0);
          algaeTable::writeData($this->getFileActions($full_filename), False);
          echo '</tr>';
        }
      }
      algaeTable::end();
    }
  }
  
  /**
   * Report details for the object.
   * {@inheritDoc}
   * @see algaeTblBase::reportDetails()
   */
  public function reportDetails()
  // --------------------------------------------------------------------------
  {
    //
    // ----- read process details if they exist
    //
    if ($this->process->rowid > 0)
    {
      $this->process->readRowFromDatabaseWithRowid($this->process->rowid);
    }
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#overview_tab', 'Overview'),
      array('#parameters_tab', 'Parameters'),
      array('#log_tab', 'Log'),
      array('#files_tab', 'Files')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- parameters_tab
    //
    echo '<div id="parameters_tab">';
    $parms_file = $this->getDirectory() . 'parameters.json';
    if (file_exists($parms_file))
    {
      algaeFile::showTextFile($parms_file);
    }
    echo '</div>';
    //
    // ----- log tab
    //
    echo '<div id="log_tab">';
    if (file_exists($this->process->logfile))
    {
      algaeFile::showTextFile($this->process->logfile);
    }
    else
    {
      echo 'Process has not been run.<p />';
    }
    echo '</div>';
    //
    // ----- files_tab
    //
    echo '<div id="files_tab">';
    $this->reportFiles();
    echo '</div>';
    //
    // ----- end of all tabs div
    //
    algaeForm::endTabs();
  }
  
  /**
   * Report a table of records.
   * {@inheritDoc}
   * @see algaeTblBase::reportRecords()
   */
  public function reportRecords($tableId = 'slateGeoProcessingTable', $whereClause = '', $maxRecords = 10000)
  // --------------------------------------------------------------------------
  {
    global $app;
    $add_link = $app->getPageLink($this->select_geoprocess_page,
      'Add a GeoProcess', algaeAccess::ROLE_WRITE, $app->settings->appName, '') . '<p />';
    $sql = $this->get_sql(True);
    $sql .= $whereClause;
    if ($this->showAddLink) { echo $add_link; }
    //
    // ----- run the query
    //
    $data = algaeDB::getArray($sql, array());
    if (count($data) > 0)
    {
      //
      // ----- initial the table
      //
      $tableId = $this->getDefaultRecordsTableId($tableId);
      algaeTable::initTablesorterJavascript($tableId, '[[1,0],[2,0]]');
      algaeTable::start($tableId, 'tablesorter', 'width:100%;');
      //
      // ----- table header
      //
      $header_array = array(
        array('Action', '10%'),
        array('Sequence', '10%'),
        array('Name', '20%'),
        array('Data Group', '12%'),
        array('Data Distribution', '10%'),
        array('Data Type', '10%'),
        array('Notes', '18%')
        // array('Status', '10%'),
      );
      algaeTable::writeHeader($header_array, True);
      //
      // ----- loop through the results
      //
      $p = new slateGeoProcess();
      foreach ($data as $row)
      {
        $p->init();
        $p->showBrowsePageLink = False;
        $p->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        algaeTable::writeData($p->getActionLinks(), False);
        algaeTable::writeData($p->sequence, False);
        algaeTable::writeData($p->getHomepageLink(), False);
        algaeTable::writeData(algaeCore::getColorBlock($p->data_group->html_color, True, $p->data_group->name), False);
        algaeTable::writeData($p->data_distribution->name);
        algaeTable::writeData($p->data_type->name);
        algaeTable::writeData(algaeCore::getStringWithLinks($p->description), False);
        // algaeTable::writeData($p->process->getStatusMessage(), False);
        echo '</tr>';
      }
      algaeTable::end();
    }
  }
  
  /**
   * Report records for the current project.
   */
  public function reportRecordsForCurrentProject($showAddLink = True)
  // --------------------------------------------------------------------------
  {
    global $app;
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)
    {
      $savedShowLink = $this->showAddLink;
      $this->showAddLink = $showAddLink;
      $this->reportRecords('slateGeoProcessingTable', ' WHERE project_rowid_fk = ' . $this->project->rowid);
      $this->showAddLink = $savedShowLink;
    }
  }
  
  /**
   * Get the number of processing steps in a project.
   * @param integer $project_rowid_fk The project rowid.
   * @return integer The number of steps.
   */
  public function getNumStepsForProject($project_rowid_fk)
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT COUNT(ps.rowid)
            FROM $this->table_name ps
            INNER JOIN sp.study_area sa ON ps.study_area_rowid_fk = sa.rowid
            WHERE sa.project_rowid_fk = $1";
    return algaeDB::getScalarInteger($sql, array($project_rowid_fk), 0);
  }
  
  /**
   * Report the processing steps for a project.
   * @param integer $project_rowid_fk The project rowid.
   */
  public function reportForProject($project_rowid_fk)
  // --------------------------------------------------------------------------
  {
    if ( ($this->getNumStepsForProject($project_rowid_fk) > 0) && 
       ($this->study_area->readRowFromDatabaseWithProjectRowid($project_rowid_fk)) )
    {
      $this->reportRecords('slateGeoProcessingTable', ' WHERE sp.project.rowid = ' . $project_rowid_fk);
    }
    else
    {
      $s = new slateShapefile();
      echo '<a href="', $s->selectpage, '?project_rowid_fk=', $project_rowid_fk, '">Upload a Shapefile</a> and setup the Study Area before adding Processing.<p />';
    }
  }
  
  /**
   * Build a parameters filename.
   */
  protected function buildParametersFilename()
  // --------------------------------------------------------------------------
  {
    if (strlen($this->process->logfile_root) > 0)
    {
      return $this->process->logfile_root . date('Ymd_Hms') . '_' . $this->process->application . '.json';
    }
    else
    {
      global $app;
      $app->errorMessage('Root not defined in ' . get_class($this) . '::' . __FUNCTION__ . '().');
    }
  }
  
  /**
   * Check before running, typically handled in the derived class.
   * @return boolean True or False.
   */
  protected function okToRun()
  // --------------------------------------------------------------------------
  {
    return True;
  }
  
  protected function runBatchProcess($parent_geoprocess)
  // --------------------------------------------------------------------------
  {
    
  }
  
  /**
   * Run the process.
   */
  public function run()
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->okToRun())
    {
      echo 'Running ', $this->name, '.<p />';
      $this->process = new algaeTblCoreProcess();
      $this->process->application = pathinfo($this->command, PATHINFO_FILENAME);
      $this->process->logfile_root = $this->getDirectory();
      $this->process->starting_url = 'run_geoprocess.php?rowid=' . $this->rowid;
      $this->process->result_url = $this->homepage . '?rowid=' . $this->rowid;
      $this->process->parmsfile = $this->getDirectory() . 'parameters.json';
      if ($this->process->createProcess())
      {
        $this->updateProcessRowid();
        //
        //
        //
        if (strlen($this->batch_parameters) > 0)
        {
          $gp = new $this->php_class();
          $gp->runBatchProcess($this);
        }
        else
        {
          echo $this->process->checkStatusButton();
          //
          // ----- setup parameters for shell script, write to a JSON parameters file
          //
          // $parms = $this->getArray();
          // $this->process->writeParametersFile($parms);
          //
          // ----- setup command and start the process
          //
          # $command = $app->scriptsFolder . $this->command . ' ' . $this->process->parmsfile . ' ' . $app->getResolution();
          
          $command = $app->settings->pythonAppsFolder . 'rungeoprocesses.py' . 
            ' --geoprocess_rowid_fk ' . strval($this->rowid) . 
            ' --resolution_rowid_fk ' . strval($app->getResolutionParameter(slateApp::RESOLUTION_ROWID));
          
          $this->process->startInBackground($command, 'show_progress.php');
        }
      }
    }
  }
  
  /**
   * Read parameters with a geoprocess rowid on the URL.
   */
  public function readFromURLWithRowid()
  // --------------------------------------------------------------------------
  {
    global $app;
    $urlVar = 'rowid';
    if (isset($_REQUEST[$urlVar]))
    {
      $this->rowid = algaeForm::cleanInput($_REQUEST[$urlVar]);
      if ($this->rowid > 0)
      {
        $this->read_row_from_database_with_rowid($this->rowid);
        if (strlen($this->command) > 0)
        {
          $this->project->read_row_from_database_with_rowid($this->project->rowid);
          return True;
        }
        else
        {
          $app->errorMessage('Problem reading the details for rowid ' . $this->rowid . ' from ' . $this->table_name . '.');
        }
      }
      else
      {
        $app->errorMessage($urlVar . ' specified on the URL is not > 0.');
      }
    }
    else
    {
      $app->errorMessage($urlVar . ' not specified on the URL.');
    }
    return False;
  }
  
  /**
   * Delete the geoprocess.
   * {@inheritDoc}
   * @see algaeTblBase::delete()
   */
  public function delete()
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_errors = 0;
    //
    // ----- delete dependents
    //
    algaeDB::deleteFromTable('sp.layer', 'geoprocess_rowid_fk', $this->rowid);
    algaeFile::deleteDirectory($this->getDirectory());
    //
    // ----- delete the geoprocess
    //
    if ($num_errors == 0)
    {
      if (algaeDB::deleteFromTable($this->table_name, 'rowid', $this->rowid))
      {
        $app->successMessage('GeoProcess ' . $this->name . ' deleted from ' . $this->table_name . '.');
        echo 'Goto the ', $app->getPageLink($this->browsepage, 'GeoProcesses', algaeAccess::ROLE_READ, $app->settings->appName, ''), ' page.<p />';
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
   * Process a form to select a new geoprocess.
   */
  public function processSelectForm()
  // --------------------------------------------------------------------------
  {
    if (isset($_POST['submit']))
    {
      if (algaeForm::validTokens(algaeForm::getDefaultToken($this)))
      {
        $geoprocess = algaeForm::cleanInput($_POST['geoprocess']);
        //
        //
        //
        if (strlen($geoprocess) > 0)
        {
          foreach ($this->available_geoprocesses as $gp)
          {
            if ($gp[0] == $geoprocess)
            {
              header("Location: " . $gp[1]);
            }
          }
        }
      }
    }
    return False;
  }
  
  /**
   * Show form to select a geoprocess.
   */
  public function showSelectForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    //
    // ----- initial tabs setup
    //
    algaeForm::startTabs(array(
      array('#geoprocess_tab', 'Geoprocess')
    ));
    echo '<div id="geoprocess_tab">';
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)
    {
      $this->project->read_row_from_database_with_rowid($this->project->rowid);
      $f = new algaeForm();
      echo 'Add a GeoProcess to ', $this->project->getHomepageLink(), '.</p>';
      //
      // ----- start the form
      //
      $f->startForm(algaeForm::getDefaultToken($this));
      echo '<input type="hidden" name="study_area_rowid_fk" value="', $this->study_area->rowid, '" />';
      // echo algaeForm::selectWithArray('geoprocess', '', array('Fry Analysis', 'Proximity to Lines'));
      // TODO: Kludge make a better default
      echo algaeForm::selectWithArray('geoprocess', 'Proximity Raster', array_column($this->available_geoprocesses, 0));
      //
      // ----- end form
      //
      echo '<p />';
      $f->endForm('Add', False);
      echo '<p /><br />';
    }
    echo '</div>';
    algaeForm::endTabs();
  }
  
  /**
   * Show what's being deleted.
   * {@inheritDoc}
   * @see algaeTblBase::showWhatsBeingDeleted()
   */
  public function showWhatsBeingDeleted()
  // --------------------------------------------------------------------------
  {
    global $app;
    echo 'Deleting GeoProcess ', $this->getHomepageLink();
    echo ' from project ', $this->project->getHomepageLink(), '.<p />';
    $num_layers = algaeDB::getScalarInteger("SELECT COUNT(rowid) FROM sp.layer WHERE geoprocess_rowid_fk = $1", array($this->rowid), 0);
    if ($num_layers > 0)
    {
      echo $num_layers, ' layer(s) will be deleted.<p />';
    } 
    echo 'The folder ', $this->getDirectory() . ' and all files in it will be deleted.<p />';
  }
  
  /**
   * Show form to edit a record.
   */
  protected function showMainTabForm($id, $form)
  // --------------------------------------------------------------------------
  {
    global $app;
    echo '<div id="', $id, '">';
    $this->project->rowid = $app->getCurrentProjectRowid();
    if ($this->project->rowid > 0)
    {
      $this->project->read_row_from_database_with_rowid($this->project->rowid);
      //
      // ----- get data if editing
      //
      if (isset($_REQUEST['rowid']))
      {
        $this->read_row_from_database_with_rowid($_REQUEST['rowid']);
      }
      //
      // ----- 
      //
      // $form->startForm(algaeForm::getDefaultToken($this)); # now done in the caller
      echo '<input type="hidden" name="project_rowid_fk" value="', $this->project->rowid, '" />';
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
      algaeTable::writeTwoColumns('Name', algaeForm::inputText('name', $this->name, 75, algaeForm::REQUIRED), False);
      //
      // ----- data group and type
      //
      algaeTable::writeTwoColumns('Data Group', 
        algaeForm::selectWithTableAndField($this->data_group->table_name, 'name', 
          $this->get_control_id('data_group_rowid_fk'), $this->data_group->name, True, [], null, True), False);
      algaeTable::writeTwoColumns('Data Distribution',
        algaeForm::selectWithTableAndField($this->data_distribution->table_name, 'name', 
          $this->get_control_id('data_distribution_rowid_fk'), $this->data_distribution->name, True, [], null, True), False);
      algaeTable::writeTwoColumns('Data Type',
        algaeForm::selectWithTableAndField($this->data_type->table_name, 'name', 
          $this->get_control_id('data_type_rowid_fk'), $this->data_type->name, True, [], null, True), False);
      //
      //
      //
      algaeTable::writeTwoColumns('Units',
        algaeForm::selectWithTableAndField($this->units->table_name, 'name', 
          $this->get_control_id('units_rowid_fk'), $this->units->name, False), False);
      algaeTable::writeTwoColumns('Num Decimals', algaeForm::inputText('num_decimals', $this->num_decimals, 10), False);
      algaeTable::writeTwoColumns('Sequence', algaeForm::inputText('sequence', $this->sequence, 10), False);
      //
      // ----- derived class fields
      //
      $this->addDerivedFieldsToForm();
      //
      // ----- description
      //
      algaeTable::writeTwoColumns('Notes', '', False);
      echo '<tr><td colspan="2">';
      echo '<textarea name="description" cols="83" rows="7">', algaeCore::toHtml($this->description), '</textarea><p />';
      echo '</td></tr>';
      algaeTable::end();
      //
      // ----- save button
      //
      echo '<p />';
      $form->submitButton('Save', False);
    }
    echo '</div>';
  }
  
  /**
   * Show batch parameters tab.
   */
  protected function showBatchParametersTab($id)
  // --------------------------------------------------------------------------
  {
    echo '<div id="', $id, '">';
    echo '<textarea name="batch_parameters" id="batch_parameters" class="fullwidth" cols="110" rows="18">', algaeCore::toHtml($this->batch_parameters), '</textarea><p />';
    algaeCore::addJavaScript("algaefw.makeTextareaCodeMirror('batch_parameters', 'javascript', 500);");
    echo '</div>';
  }
  
  /**
   * Show existing records tab.
   */
  protected function showExistingRecordsTab($id)
  // --------------------------------------------------------------------------
  {
    echo '<div id="', $id, '">';
    $this->reportRecordsForCurrentProject(False);
    echo '</div>';
  }
  
  public function showForm()
  // --------------------------------------------------------------------------
  {
    $f = new algaeForm();
    $mainTabId = 'main_tab';
    $batchParametersId = 'batch_parameters_tab';
    $existingRecordsId = 'existing_records_tab';
    
    $this->decodeParameters();
    //
    // ----- setup tabs
    //
    $tabs_array = array(array('#' . $mainTabId, $this->mainTabName));
    if ($this->showBatchParametersTab)
    {
      $tabs_array[] = array('#' . $batchParametersId, 'Batch Parameters');
    }
    $tabs_array[] = array('#' . $existingRecordsId, 'Existing GeoProcesses');
    
    $f->startForm(algaeForm::getDefaultToken($this));
    
    algaeForm::startTabs($tabs_array);
    //
    //
    //
    $this->showMainTabForm($mainTabId, $f);
    if ($this->showBatchParametersTab)
    {
      $this->showBatchParametersTab($batchParametersId);
    }
    $this->showExistingRecordsTab($existingRecordsId);
    //
    // ----- end tabs
    //
    algaeForm::endTabs();
    //
    // ----- end form
    //
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
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
        parent::post_control_data();
        
        $this->project->rowid = algaeForm::cleanInput($_POST['project_rowid_fk']);
        $this->project->read_row_from_database_with_rowid($this->project->rowid);
        
        /**
        if (isset($_POST['rowid']))
        {
          $this->rowid = algaeForm::cleanInput($_POST['rowid']);
        }
        $this->project->rowid = algaeForm::cleanInput($_POST['project_rowid_fk']);
        $this->project->read_row_from_database_with_rowid($this->project->rowid);
        $this->name = algaeForm::cleanInput($_POST['name']);
        $this->data_group->name = algaeForm::cleanInput($_POST['data_group']);
        $this->data_distribution->name = algaeForm::cleanInput($_POST['data_distribution']);
        $this->data_type->name = algaeForm::cleanInput($_POST['data_type']);
        $this->units->name = algaeForm::cleanInput($_POST['units']);
        $this->num_decimals = algaeForm::cleanInput($_POST['num_decimals']);
        if (strlen($this->num_decimals) == 0)
        {
          $this->num_decimals = 0;
        }
        $this->sequence = algaeForm::cleanInput($_POST['sequence']);
        $this->description = algaeForm::cleanInput($_POST['description']);
        if (isset($_POST['batch_parameters']))
        {
          $this->batch_parameters = algaeForm::cleanInput($_POST['batch_parameters']);
        }
        */
        $this->processDerivedVariables();
        //
        // ----- 
        //
        if (isset($_REQUEST['rowid']))
        {
          # $this->debug = true;
          if ($this->ok_to_update())
          {
            if ($this->update())
            {
              header("Location: {$this->browsepage}?message=" . urlencode('GeoProcess ' . $this->name . ' updated.'));
            }
            else
            {
              $app->errorMessage('Problem updating the GeoProcess.');
            }
          }
        }
        else
        {
          if ($this->ok_to_insert())
          {
            if ($this->insert())
            {
              header("Location: {$this->browsepage}?message=" . urlencode('GeoProcess ' . $this->name . ' added.'));
            }
            else
            {
              $app->errorMessage('Problem adding the GeoProcess.');
            }
          }
        }
      }
    }
    $this->showForm();
    return False;
  }
  
  /**
   * Get a layer file associated with the geoprocess.
   * @param integer $resolution Resolution, default project resolution if not specified.
   * @return string Fully qualified filename or null.
   */
  public function getLayerFilename($resolution)
  // --------------------------------------------------------------------------
  {
    global $app;
    if ($this->rowid > 0)
    {
      $this->readRowFromDatabaseWithRowid($this->rowid);
      if ($resolution == null)
      {
        $resolution = $app->getResolution();
        $sql = "SELECT l.filename
                FROM sp.layer l
                WHERE l.geoprocess_rowid_fk = $1 AND l.resolution = $2";
        $filename = algaedB::getScalarString($sql, array($this->rowid, $resolution));
        if (strlen($filename) > 0)
        {
          return $this->getDirectory() . $filename;
        }
      }
      
    }
    return null;
  }
  
}




