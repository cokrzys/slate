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
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->config->app_folder = 'slate';
    $this->addAppSpecificClasses();
    $this->config->app_name = 'slate';
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
    require_once 'slateProject.php';
  }
  
  /**
   * Application level menu.
   */
  protected function addMenu()
  // --------------------------------------------------------------------------
  {
    echo $this->getPageLink('home.php', 'slate', algaeAccess::ROLE_READ, $this->config->app_name);
    echo $this->getPageLink('project.php', 'Project', algaeAccess::ROLE_READ, $this->config->app_name);
    /*
    echo $this->getPageLink('browse_places.php', 'Places', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('browse_data.php', 'Data', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('browse_geoprocesses.php', 'GeoProcesses', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('browse_layers.php', 'Layers', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('browse_maps.php', 'Maps', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('reports.php', 'Reports', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('utilities.php', 'Utilities', algaeAccess::ROLE_READ, $this->settings->appName);
    echo $this->getPageLink('edit_setup.php', 'Setup', algaeAccess::ROLE_READ, $this->settings->appName, '');
    */
    /*
     echo '<span class="align_right">';
     $current_project = $this->getCurrentProjectName();
     if (strlen($current_project) > 0)
     {
     echo 'Current project: ', $current_project, $this->settings->menuSeparator;
     echo $this->getPageLink('edit_setup.php', 'Change', algaeAccess::ROLE_READ, $this->settings->appName, '');
     }
     echo '</span>';
     */
    echo '<p />';
  }
  
  /**
   * Get the current project as a rowid.
   * @return string Rowid as a string or null.
   */
  public function getCurrentProjectRowid($showErrorMessage = True)
  // --------------------------------------------------------------------------
  {
    $rowid = algaeTblCoreUserParameter::getParameter(slateApp::CURRENT_PROJECT_ROWID_PARAMETER_NAME);
    if (($rowid <= 0) || ($rowid == null))
    {
      $this->errorMessage('Current project is not defined.');
    }
    return $rowid;
  }
  
}




