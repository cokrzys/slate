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
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->config->app_folder = 'sladah';
    $this->addAppSpecificClasses();
    $this->config->app_name = 'slate';
  }
  
  /**
   * Add application specific classes.
   */
  protected function addAppSpecificClasses()
  // --------------------------------------------------------------------------
  {
    parent::addAppSpecificClasses();
  }
  
}




