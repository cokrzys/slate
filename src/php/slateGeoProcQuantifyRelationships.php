<?php

/**

  slate | Create quantify relationships GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcQuantifyRelationships extends slateGeoProcess
{
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->init();
  }
  
  public function init()
  // --------------------------------------------------------------------------
  {
    parent::init();
    $this->command = 'quantify_relationships.sh';
    $this->name = 'Quantify Relationships';
    $this->mainTabName = $this->name;
    //
    //
    //
    $this->data_group->name = 'Statistics';
    $this->data_distribution->name = 'Statistics';
  }
  
}





