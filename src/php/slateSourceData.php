<?php

/**

  slate | Source data support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateSourceData
{
  
  CONST RASTER = 0;
  CONST VECTOR = 1;
  CONST OTHER = 2;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
  }
  
  protected function showTab($tab_id, $type)
  // --------------------------------------------------------------------------
  {
    echo '<div id="', $tab_id, '">';
    if ($type == slateSourceData::RASTER)
    {
      echo 'TODO<p />';
      // $f = new slateFile();
      // $f->reportRecordsForCurrentProject();
    }
    elseif ($type == slateSourceData::VECTOR) 
    {
      $s = new slateShapefile();
      $s->reportRecordsForCurrentProject();
    }
    if ($type == slateSourceData::OTHER)
    {
      echo 'TODO<p />';
      // $f = new slateFile();
      // $f->reportRecordsForCurrentProject();
    }
    echo '</div>';
  }
  
  public function showForm()
  // --------------------------------------------------------------------------
  {
    algaeForm::startTabs(array(
      array('#raster_tab', 'Raster'),
      array('#vector_tab', 'Vector'),
      array('#other_tab', 'Other')
    ));
    $this->showTab('#raster_tab', slateSourceData::RASTER);
    $this->showTab('#vector_tab', slateSourceData::VECTOR);
    $this->showTab('#other_tab', slateSourceData::OTHER);
    algaeform::endTabs();
  }
  
}




