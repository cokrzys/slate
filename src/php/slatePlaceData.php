<?php

/**

  slate | A data value associated with a place.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slatePlaceData extends algaeTblDataValueBase
{
    
  /**
   * Constructor.
   */
  public function __construct($parentClass = 'slatePlace', $parentColumnName = 'place_rowid_fk', 
    $fieldClass = 'algaeTblField', $fieldColumnName = 'field_rowid_fk')
  // --------------------------------------------------------------------------
  {
    parent::__construct($parentClass, $parentColumnName, $fieldClass, $fieldColumnName);
    $this->table_name = 'sp.place_data';
  }
  
}
