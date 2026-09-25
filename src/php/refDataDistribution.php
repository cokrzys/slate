<?php

/**

  slate | Support for data distribution types and the ref.data_distribution table.
  
  Data distribution is used to define how the data is broadly distributed or occurrs.
  Examples include Sequential, Categorical, and Shapefile.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refDataDistribution extends algaeTblReferenceBase
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
    $this->table_name = 'ref.data_distribution';
    $this->homepage = 'data_distribution.php';
    $this->editpage = 'edit_data_distribution.php';
    $this->itemName = 'Data Distribution';
  }
  
}
