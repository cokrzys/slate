<?php

/**

  slate | Application base class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateApp extends algaeApp
{
  
  const CURRENT_PROJECT_ROWID_PARAMETER_NAME = 'currentProjectRowidParameterName';
  
  const RESOLUTION_OBJECT = 0;
  const RESOLUTION_ROWID = 1;
  const RESOLUTION_NAME = 2;
  const RESOLUTION_FOLDER = 3;
  const RESOLUTION_X = 4;
  const RESOLUTION_Y = 5;
  
  /**
   * Constructor.
   */
  public function __construct($load_detailed_config = True, $debug = False)
  // --------------------------------------------------------------------------
  {
    //
    // ----- order is important
    //
    // __construct(False) | framework app init, config loaded but not detailed
    // addAppSpecificClasses() | loads slateConfig class
    // $this->config = new slateConfig(); | read all framework and app config
    //
    parent::__construct(False);
    $this->addAppSpecificClasses();
    $this->config = new slateConfig();
  }
  
  /**
   * Add application specific classes.
   */
  protected function addAppSpecificClasses()
  // --------------------------------------------------------------------------
  {
    parent::addAppSpecificClasses();
    require_once 'slateConfig.php';
    require_once 'refDataDistribution.php';
    require_once 'refDataGroup.php';
    require_once 'refDataLocation.php';
    require_once 'refDataType.php';
    require_once 'refResolution.php';
    require_once 'refGeometryType.php';
    require_once 'refUnits.php';
    require_once 'refFileGroup.php';
    require_once 'refFileFormat.php';
    require_once 'slateProject.php';
    require_once 'slateSourceData.php';
    require_once 'slateSourceFile.php';
    require_once 'slateStudyArea.php';
    require_once 'slateGeoProcess.php';
    require_once 'slatePlace.php';
  }
  
  /**
   * Application level menu.
   */
  protected function addMenu()
  // --------------------------------------------------------------------------
  {
    echo $this->getPageLink($this->getURLBase() . 'home.php', 'slate', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink($this->getURLBase() . 'project.php', 'Project', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink($this->getURLBase() . 'browse_source_data.php', 'Data', algaeAccess::ROLE_READ, $this->config->app_name);
    
    echo $this->getPageLink($this->getURLBase() . 'utilities.php', 'Utilities', algaeAccess::ROLE_READ, $this->config->app_name, '');
    
    /*
    echo $this->getPageLink('browse_places.php', 'Places', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('browse_data.php', 'Data', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('browse_geoprocesses.php', 'GeoProcesses', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('browse_layers.php', 'Layers', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('browse_maps.php', 'Maps', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('reports.php', 'Reports', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('edit_setup.php', 'Setup', algaeAccess::ROLE_READ, $this->config->app_name, '');
    */
    /*
     echo '<span class="align_right">';
     $current_project = $this->getCurrentProjectName();
     if (strlen($current_project) > 0)
     {
     echo 'Current project: ', $current_project, $this->settings->menuSeparator;
     echo $this->getPageLink('edit_setup.php', 'Change', algaeAccess::ROLE_READ, $this->config->app_name, '');
     }
     echo '</span>';
     */
    echo '<p />';
  }
  
  /**
   * Utilities menu.
   */
  public function utilitiesMenu()
  // --------------------------------------------------------------------------
  {
    echo '<ul>';
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_geoprocesses_batch.php', '[TODO] Setup and Run a Batch of GeoProcesses', algaeAccess::ROLE_WRITE, $this->config->app_name, ''), '</li>';
    echo '</ul>';
    echo '<div style="margin-left:1em;">Add or Edit<p /></div>';
    echo '<ul>';
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_data_distribution.php', 'Data Distributions', algaeAccess::ROLE_WRITE, 
      $this->config->app_name, ''), $this->getDetailString('Categorical, Sequential'), '</li>';
    
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_data_group.php', 'Data Groups', algaeAccess::ROLE_WRITE, 
      $this->config->app_name, ''), $this->getDetailString('Geology, Geophysics, Cultural'), '</li>';
  
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_data_type.php', 'Data Type', algaeAccess::ROLE_WRITE, 
      $this->config->app_name, ''), $this->getDetailString('Byte, Float32, Shapefile'), '</li>';
  
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_file_format.php', 'File Format', algaeAccess::ROLE_WRITE,
      $this->config->app_name, ''), $this->getDetailString('shp, tiff, csv'), '</li>';
  
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_file_group.php', 'File Group', algaeAccess::ROLE_WRITE, 
      $this->config->app_name, ''), $this->getDetailString('Raster, Vector, Documentation'), '</li>';
  
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_resolution.php', 'Resolutions', algaeAccess::ROLE_WRITE, 
      $this->config->app_name, ''), $this->getDetailString('Cell Size | 50 Meters, 1000 Meters'), '</li>';
  
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_task.php', '[TODO] Tasks', algaeAccess::ROLE_ADMIN, $this->config->app_name, ''), '</li>';
    echo '<li>', $this->getPageLink($this->getURLBase() . 'edit_units.php', '[TODO] Units', algaeAccess::ROLE_WRITE, $this->config->app_name, ''), '</li>';
    echo '</ul>';
  }
  
  /**
   * Get the current project as a rowid.
   * @return string Rowid as a string or null.
   */
  public function getCurrentProjectRowid($showErrorMessage = True)
  // --------------------------------------------------------------------------
  {
    $rowid = algaeTblCoreUserParameter::get_parameter(slateApp::CURRENT_PROJECT_ROWID_PARAMETER_NAME);
    if (($rowid <= 0) || ($rowid == null))
    {
      $this->errorMessage('Current project is not defined.');
    }
    return $rowid;
  }
  
  public function getResolutionParameter($parameter_name)
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT r.rowid
            FROM sp.study_area sa
            INNER JOIN ref.resolution r ON sa.resolution_rowid_fk = r.rowid
            WHERE project_rowid_fk = $1";
    $rowid = algaeDB::getScalarInteger($sql, array($this->getCurrentProjectRowid()), null);
    if ($rowid != null)
    {
      $res = new refResolution();
      $res->read_row_from_database_with_rowid($rowid);
      if ($res->name != null)
      {
        if ($parameter_name == slateApp::RESOLUTION_OBJECT) {
          return $res;
        }
        if ($parameter_name == slateApp::RESOLUTION_ROWID) {
          return $res->rowid;
        }
        if ($parameter_name == slateApp::RESOLUTION_NAME) {
          return $res->name;
        }
        elseif ($parameter_name == slateApp::RESOLUTION_FOLDER) {
          return $res->folder;
        }
        elseif ($parameter_name == slateApp::RESOLUTION_X) {
          return $res->cell_size_x;
        }
        elseif ($parameter_name == slateApp::RESOLUTION_Y) {
          return $res->cell_size_y;
        }
        else {
          algaeApp::errorMessage('Unknown resolution parameter ' . $parameter_name . '.');
        }
      }
    }
    return null;
  }
  
  /**
   * Get the default resolution name.
   * @return string Typically 'Low' or 'High'.
   */
  public function getResolutionName()
  // --------------------------------------------------------------------------
  {
    return $this->getResolutionParameter(slateApp::RESOLUTION_NAME);
  }
  
  public function getResolutionFolder()
  // --------------------------------------------------------------------------
  {
    return $this->getResolutionParameter(slateApp::RESOLUTION_FOLDER);
  }
  
  /**
   * Get the default resolution.
   * @return integer|number
   */
  public function getResolution()
  // --------------------------------------------------------------------------
  {
    return $this->getResolutionParameter(slateApp::RESOLUTION_X);
  }
  
}




