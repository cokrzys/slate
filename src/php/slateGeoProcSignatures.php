<?php

/**

  slate | Create signatures GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcSignatures extends slateGeoProcess
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
    $this->command = 'create_signatures.sh';
    $this->name = 'Signatures';
    $this->mainTabName = 'Signatures';
    //
    //
    //
    $this->data_group->name = 'Mask';
    $this->data_distribution->name = 'Categorical';
  }
  
}





