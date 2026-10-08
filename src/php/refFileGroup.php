<?php

/**

  slate | Support for file group classifications and the ref.file_group table.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refFileGroup extends algaeTblReferenceBase
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
    $this->table_name = 'ref.file_group';
    $this->homepage = 'file_group.php';
    $this->editpage = 'edit_file_group.php';
    $this->itemName = 'File Group';
  }
  
}
