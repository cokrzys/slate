<?php

/**

  slate | Place and sp.place support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slatePlace extends algaeTblNamedObjectBase
{
  
  public $project;
  public $abbreviation;
  public $latitude;
  public $longitude;
  
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
    $this->table_name = 'sp.place';
    $this->homepage = 'place.php';
    $this->editpage = 'edit_place.php';
    $this->browsepage = 'browse_places.php';
    $this->itemName = 'Place';
    $this->project = new slateProject();
    $this->latitude = null;
    $this->longitude = null;
    $this->abbreviation = null;
  }
  
  public function getDirectory()
  # ---------------------------------------------------------------------------
  {
    global $app;
    return $this->project->getItemDirectory($app->getResolutionName(), $app->config->places_folder, $this->rowid);
  }
  
  /**
   * Echo a one line link like "Goto the XYZ homepage.".
   * @param boolean $new_page True (default) to open homepage on a new tab.
   */
  public function echoOneLineHomepageLink($new_page = True)
  // --------------------------------------------------------------------------
  {
    echo 'Goto the ', $this->getHomepageLink($this->name, algaeAccess::ROLE_READ, $new_page), ' homepage.<p />';
  }
  
  /**
   * Process derived class form data.
   * {@inheritDoc}
   * @see algaeTblBase::processDerivedVariables()
   */
  protected function processDerivedVariables()
  // --------------------------------------------------------------------------
  {
    global $app;
    if ( (! isset($this->project->rowid)) || ($this->project->rowid <= 0) )
    {
      $this->project->rowid = $app->getCurrentProjectRowid();
    }
  }
  
  /**
   * Add derived class fields to the standard form.
   * {@inheritDoc}
   * @see algaeTblBase::addDerivedFieldsToForm()
   */
  protected function  addDerivedFieldsToForm()
  // --------------------------------------------------------------------------
  {
    parent::addDerivedFieldsToForm();
    $onClick = "algaeMap.showMapDialog('latitude', 'longitude', 'zoom_level');";
    $pick_center_dialog_html = algaeForm::getClickableImage('/algae/img/map_marker_icon_32px.png', 20, 20, True, $onClick);
    algaeTable::writeTwoColumns('Abbreviation', algaeForm::inputText('abbreviation', $this->abbreviation, 10, algaeForm::REQUIRED), False);
    algaeTable::writeTwoColumns('Latitude', algaeForm::inputText('latitude', algaeCore::getFormattedNumber($this->latitude, 5, null), 15) . $pick_center_dialog_html, False);
    algaeTable::writeTwoColumns('Longitude', algaeForm::inputText('longitude', algaeCore::getFormattedNumber($this->longitude, 5, null), 15). $pick_center_dialog_html, False);
  }
  
  /**
   * Add derived fields to the overall details table.
   * {@inheritDoc}
   * @see algaeTblBase::addDerivedFieldsToOverallDetails()
   */
  protected function addDerivedFieldsToOverallDetails()
  // --------------------------------------------------------------------------
  {
    algaeTable::writeTwoColumns('Project', $this->project->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Abbreviation', $this->abbreviation);
    algaeTable::writeTwoColumns('Folder', $this->getDirectory());
    algaeTable::writeTwoColumns('Latitude', $this->latitude);
    algaeTable::writeTwoColumns('Longitude', $this->longitude);
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
    /*
    if ($this->isTextFile($filename))
    {
      $html .= $app->settings->menuSeparator;
      $html .= $app->getPageLink('view_text_file.php?filename=' . urlencode($filename), 'View', algaeAccess::ROLE_READ, $app->settings->appName, '', True);
    }
    */
    return $html;
  }
  
  /**
   * Report files for the process.
   */
  protected function reportFiles()
  // --------------------------------------------------------------------------
  {
    $folder = $this->getDirectory();
    $files = scandir($folder);
    if (sizeof($files) > 2)
    {
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
          $full_filename = $folder . $file;
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
    else 
    {
      echo 'No file(s) in ', $folder, '<p />';
    }
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
      array('#map_tab', 'Map'),
      array('#signature_tab', 'Signature'),
      array('#files_tab', 'Files')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- map_tab
    //
    echo '<div id="map_tab">';
    /*    
    $study_area = new slateStudyArea();
    $study_area->readRowFromDatabaseWithProjectRowid($app->getCurrentProjectRowid());
    $study_area->setMapBounds($map);
    $map->startMapFile();
    $name = pathinfo($this->filename, PATHINFO_FILENAME);
    $map->addRasterLayer($this->getFullyPathedRasterForWebMap(), $name, $name, 1, $study_area->srid_fk, 70);
    $map->endMapFile();
    $map->show();
    */
    echo '</div>';
    //
    // ----- signature_tab
    //
    echo '<div id="signature_tab">';
    $s = new slateSignature();
    $s->reportPlaceSignature($this);
    echo '</div>';
    //
    // ----- files_tab
    //
    echo '<div id="files_tab">';
    $this->reportFiles();
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs();
  }
  
  public function reportExistingRecords()
  // --------------------------------------------------------------------------
  {
    $this->reportRecordsForCurrentProject();
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
      $this->reportRecords('slatePlacesTable', ' WHERE project_rowid_fk = ' . $this->project->rowid);
    }
  }
  
}