<?php

/**

  slate | Homepage support.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateHomepage
{
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
  }
  
  /**
   * https://stackoverflow.com/questions/478121/how-to-get-directory-size-in-php
   * @param string $path
   * @return number
   */
  protected function GetDirectorySize($path)
  {
    $bytestotal = 0;
    $path = realpath($path);
    if($path!==false && $path!='' && file_exists($path)){
      foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
        $bytestotal += $object->getSize();
      }
    }
    return $bytestotal;
  }
  
  protected function showProjectSize()
  {
    global $app;
    $p = new slateProject();
    $p->read_row_from_database_with_rowid($app->getCurrentProjectRowid());
    $size = $this->GetDirectorySize($p->getDirectory());
    echo algaeFile::getHumanFilesize($size, 2), '<p />';
  }
  
  protected function showProjectSummary()
  {
    global $app;
    $p = new slateProject();
    $p->read_row_from_database_with_rowid($app->getCurrentProjectRowid());
    
    $resolution_name = $app->getResolutionName();
    if (! isset($resolution_name))
    {
      $resolution_name = 'Not Set';
    }
    $size = $this->GetDirectorySize($p->getDirectory());
    
    algaeTable::start('formTable', 'algae_form_table', '');
    algaeTable::writeHeader(array(), False);
    
    algaeTable::writeTwoColumns('Current Project', $p->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Resolution', $resolution_name, False);
    algaeTable::writeTwoColumns('Folder', $p->getDirectory(), False);
    algaeTable::writeTwoColumns('Size on Disk', algaeFile::getHumanFilesize($size, 2), False);
    
    algaeTable::end();
  }
  
  /**
   * Show the homepage.
   */
  public static function show()
  // --------------------------------------------------------------------------
  {
    //
    // ----- search form
    //
    /*
    $s = new stocksSearch();
    $s->processSearchForm();
    $s->showSearchForm();
    */
    //
    // ----- tabbed report
    //
    $tabs_array = array(array('#summary_tab', 'Summary'));;
    algaeForm::startTabs($tabs_array);
    //
    // ----- summary_tab
    //
    echo '<div id="summary_tab">';
    $h = new slateHomepage();
    $h->showProjectSummary();
    // $h->showProjectSize();
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs('tabs');
  }
  
}