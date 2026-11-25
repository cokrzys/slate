<?php

/**

  slate | Signature and support for sp.signature table.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateSignature extends algaeTblBase
{
  
  public $layer;
  public $place;
  public $numeric_val;
  
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
    $this->table_name = 'sp.signature';
    $this->numeric_val = null;
    $this->layer = new slateLayer;
    $this->place = new slatePlace;
  }
  
  public function calcSignature()
  // --------------------------------------------------------------------------
  {
    global $app;
    algaeForm::startSingleTab('Calc Signature');
    if (isset($_REQUEST['place_rowid_fk']))
    {
      $place_rowid_fk = algaeForm::cleanInput($_REQUEST['place_rowid_fk']);
      $output=null;
      //
      //
      //
      $o = new slatePlace();
      $o->read_row_from_database_with_rowid($place_rowid_fk);
      $o->echoOneLineHomepageLink(False);
      //
      // ----- run python app, NOT in background
      //       exec() = terminal output goes to $output as an array of lines
      //       2>&1 = sends both messages and errors to $output
      //
      // createsignature.py --place_rowid_fk 13 --resolution 100000
      //
      $cmd = 'createsignature.py --place_rowid_fk ' . strval($place_rowid_fk) . 
        ' --resolution_rowid_fk ' . strval($app->getResolutionParameter(slateApp::RESOLUTION_ROWID));
      exec($app->settings->pythonAppsFolder . $cmd . " 2>&1", $output);
      algaeApp::showExecOutput($output);
    }
    else
    {
      $app->errorMessage('Place rowid not specified on the URL.');
    }
    algaeForm::endSingleTab();
  }
  
  /**
   * Get HTML link to update the signature.
   * @param string $promptPrefix Typically 'Calc' or 'Update'.
   * @return string Completed HTML for link.
   */
  public function getCalcSignatureLink($place_rowid_fk, $promptPrefix = 'Calc')
  // --------------------------------------------------------------------------
  {
    global $app;
    $linkText = $promptPrefix . ' Signature';
    return $app->getPageLink($app->getURLBase() . 'calc_signature.php?place_rowid_fk=' . $place_rowid_fk, $linkText,
      algaeAccess::ROLE_READ, $app->config->app_name, '');
  }
  
  /***
   * 
   */
  protected function displayPlaceSignature($place, $data)
  // --------------------------------------------------------------------------
  {
    $tableId = 'signatureTable';
    algaeTable::initTablesorterJavascript($tableId, '[[0,0]]');
    algaeTable::start($tableId, 'tablesorter', 'width:75%;');
    //
    // ----- setup associative array with header details
    //
    $header_array = array();
    $header_array[] = array('name'=>'Layer', 'width'=>'40%');
    $header_array[] = array('name'=>'Group', 'width'=>'15%');
    $header_array[] = array('name'=>'Distribution', 'width'=>'15%');
    $header_array[] = array('name'=>'Signature', 'width'=>'15%');
    //
    // ----- write headers
    //
    algaeTable::writeHeaderWithAssociativeArray($header_array);
    //
    // ----- loop through the results
    //
    foreach ($data as $row)
    {
      $layer = new slateLayer();
      $layer->read_row_from_database_with_rowid($row[0]);
      echo '<tr>';
      algaeTable::writeData($layer->getHomepageLink($layer->geoprocess->name), False);
      algaeTable::writeData(algaeCore::getColorBlock($layer->geoprocess->data_group->html_color, 
        True, $layer->geoprocess->data_group->name), False);
      algaeTable::writeData($layer->geoprocess->data_distribution->name);
      algaeTable::writeData($layer->getFormattedDisplayValue($row[1]), False, '', '', '', $row[1]);
      echo '</tr>';
    }
    algaeTable::end();
  }
  
  public function reportPlaceSignature($place)
  // --------------------------------------------------------------------------
  {
    global $app;
    $sql = "SELECT layer_rowid_fk, numeric_val
            FROM sp.signature s
            INNER JOIN sp.layer l ON s.layer_rowid_fk = l.rowid AND resolution_rowid_fk = $2
            WHERE place_rowid_fk = $1";
    $parms = array($place->rowid, $app->getResolutionRowid());
    $data = algaeDB::getArray($sql, $parms);
    if (($data != null) && (count($data) > 0))
    {
      $this->displayPlaceSignature($place, $data);
    }

  }
  
  public static function reportSignaturesForLayer($layer_rowid_fk)
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT rowid
            FROM sp.signature
            WHERE layer_rowid_fk = $1";
    $data = algaeDB::getArray($sql, array($layer_rowid_fk));
    if (count($data) > 0)
    {
      //
      // ----- initial the table
      //
      $tableId = 'layerSignaturesTable';
      algaeTable::initTablesorterJavascript($tableId, '[[0,0]]');
      algaeTable::start($tableId, 'tablesorter', 'width:50%;');
      //
      // ----- setup associative array with header details
      //
      $header_array = array();
      $header_array[] = array('name'=>'Place', 'width'=>'50%');
      $header_array[] = array('name'=>'Signature', 'width'=>'50%');
      //
      // ----- write headers
      //
      algaeTable::writeHeaderWithAssociativeArray($header_array);
      //
      // ----- loop through the results
      //
      $s = new slateSignature();
      foreach ($data as $row)
      {
        $s->init();
        $s->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        algaeTable::writeData($s->place->getHomepageLink(), False);
        algaeTable::writeData($s->layer->getFormattedDisplayValue($s->numeric_val), False);
        echo '</tr>';
      }
      algaeTable::end();
    }
    else
    {
      echo 'Nothing to report.<p />';
    }
  }
  
}