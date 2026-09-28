<?php

  /**
    Slate place homepage.
    
    @author    Brian Krzys (cokrzys@gmail.com)
    @copyright 2020 RTSpatial Ltd.
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
  $app->isSufficientRights(algaeAccess::ROLE_READ, $app->settings->appName);
  //
  // ----- initial the html page
  //
  $title = 'Place';
  $app->startPage($title);
  $app->showHeader($title);
  //
  // ----- page content
  //
  $o = new slatePlace();
  $o->showHomepage();
  //
  // ----- finish up and close page
  //
  $app->showFooter();
  $app->closePage();
