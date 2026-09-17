<?php

  /**
  
    slate | Query tool.
    
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
  $app->isSufficientRights(algaeAccess::ROLE_ADMIN, $app->config->app_name);
  //
  // ----- initial the html page
  //
  $title = 'Query Tool';
  $app->startPage($title, '', False);
  $app->addJavaScriptLibrary('/algae/js/algaeQueryTool.js');
  $app->closeHeadSection();
  $app->showHeader($title);
  //
  // ----- page content
  //
  $qt = new algaeQueryTool();
  $qt->show();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();

