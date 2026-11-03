<?php

  /**
  
   slate | Setup a batch of geoprocesses to run.
    
   For example run all geoprocesses for a project or a range of sequence numbers.
    
   @author    Brian Krzys (brian.krzys@rtspatial.com)
   @copyright (c) 2026 RTSpatial Ltd.
   @license   SPDX-License-Identifier: MIT
   @link      https://github.com/cokrzys/slate
  
  */

  //
  // ----- initial the algae framework and application
  //
  require_once 'algaeApp.php';
  algaeApp::addAppIncludesPath('slate');
  require_once 'slateApp.php';
  $app = new slateApp();
  require_once 'slateBatchRunGeoprocesses.php';
  //
  // ----- check login and rights
  //
  algaeAccess::isLoggedIn();
  $app->readRoles();
  $app->isSufficientRights(algaeAccess::ROLE_WRITE, $app->config->app_name);
  //
  // ----- initial the html page
  //
  $title = 'Setup GeoProcesses Batch Run';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  $o = new slateBatchRunGeoprocesses();
  $o->showPage();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();