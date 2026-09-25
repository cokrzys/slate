<?php

/**

  slate | Support for data groups and the ref.data_group table.
  
  Data groups are a primary data classifier.  
  Examples include Climatological, Mineral Occurrences, and Structure.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refDataGroup extends algaeTblReferenceBase
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
    $this->table_name = 'ref.data_group';
    $this->homepage = 'data_group.php';
    $this->editpage = 'edit_data_group.php';
    $this->itemName = 'Data Group';
  }
  
}
