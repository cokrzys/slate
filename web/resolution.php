<?php

  /**
  
    slate | Resolution homepage.
    
    @author    Brian Krzys (brian.krzys@rtspatial.com)
    @copyright (c) 2026 RTSpatial Ltd.
    @license   SPDX-License-Identifier: MIT
    @link      https://github.com/cokrzys/algae
  
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
  $app->isSufficientRights(algaeAccess::ROLE_READ, $app->config->app_name);
  //
  // ----- initial the html page
  //
  $title = 'Resolution';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  $o = new refResolution();
  $o->showHomepage();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();
