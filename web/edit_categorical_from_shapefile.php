<?php

  /**
  
    slate | Edit categorical from shapefile  geoprocessing step.
    
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
  //
  // ----- check login and rights
  //
  algaeAccess::isLoggedIn();
  $app->readRoles();
  $app->isSufficientRights(algaeAccess::ROLE_WRITE, $app->config->app_name);
  //
  // ----- initial the html page
  //
  $title = 'Categorical from Shapefile';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  $o = new slateGeoProcCategorical();
  $o->processForm();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();