<?php

  /**
    Slate browse places.
    
    @author    Brian Krzys (cokrzys@gmail.com)
    @copyright 2026 RTSpatial Ltd.
    @license   https://opensource.org/licenses/MIT
    @link      https://github.com/cokrzys/slate
  */

  //
  // ----- initial the algae framework and application
  //
  require_once 'algaeApp.php';
  require_once 'classes/slateApp.php';
  $app = new slateApp();
  //
  // ----- check login and rights
  //
  algaeAccess::isLoggedIn();
  $app->readRoles();
  $app->isSufficientRights(algaeAccess::ROLE_WRITE, $app->settings->appName);
  //
  // ----- initial the html page
  //
  $title = 'Places';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  algaeForm::startSingleTab($title);
  //
  // ----- shapefiles_tab
  //
  $o = new slatePlace();
  $o->reportRecordsForCurrentProject();
  algaeform::endSingleTab();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();