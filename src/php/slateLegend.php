<?php

/**

  slate | Legend sp.legend support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateLegend extends algaeTblNamedObjectBase
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
    $this->table_name = 'sp.legend';
    $this->homepage = 'legend.php';
    $this->editpage = 'edit_legend.php';
    $this->browsepage = 'browse_legends.php';
    $this->deletepage = 'delete_legend.php';
    $this->itemName = 'Legend';
  }
  
}