<?php

  /**
  
    slate | Default homepage.
    
    @author    Brian Krzys (brian.krzys@rtspatial.com)
    @copyright (c) 2026 RTSpatial Ltd.
    @license   SPDX-License-Identifier: MIT
    @link      https://github.com/cokrzys/slate
  
  */

  //
  // ----- initial the application
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
  $title = 'Home';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  algaeForm::startSingleTab($title);
  echo 'Hello from slate.', PHP_EOL;
  algaeForm::endSingleTab();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();


