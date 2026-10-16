<?php

/**

  slate | Place report.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slatePlaceReport extends algaeReport
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
  
  /**
   * Initial default values.
   */
  public function init()
  // --------------------------------------------------------------------------
  {
    parent::init();
    $this->name = 'PlacesReport';
    $this->master_class = 'slatePlace';
    $this->data_class = 'slatePlaceData';
    $this->parent_rowid_field = 'place_rowid_fk';
    $this->results_tab_name = 'Places';
    $this->first_column_name = 'Place';
  }
  
}




