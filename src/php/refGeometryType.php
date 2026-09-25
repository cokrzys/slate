<?php

/**

  slate | Support for geometry types and the ref.geometry_type table.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refGeometryType extends algaeTblReferenceBaseV2
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
    $this->table_name = 'ref.geometry_type';
    $this->homepage = 'geometry_type.php';
    $this->editpage = 'edit_geometry_type.php';
    $this->itemName = 'Geometry Type';
  }
  
}
