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
  protected function displayPlaceSignature($ref_place, $place, $data)
  // --------------------------------------------------------------------------
  {
    global $app;
    $tableId = 'signatureTable';
    algaeTable::initTablesorterJavascript($tableId, '[[0,0]]');
    algaeTable::start($tableId, 'tablesorter', 'width:100%;');
    //
    // ----- setup associative array with header details
    //
    $header_array = array();
    $header_array[] = array('name'=>'Layer', 'width'=>'30%');
    $header_array[] = array('name'=>'Group', 'width'=>'15%');
    $header_array[] = array('name'=>'Distribution', 'width'=>'10%');
    $header_array[] = array('name'=>$place->name, 'width'=>'18%');
    $header_array[] = array('name'=>$ref_place->name, 'width'=>'18%');
    // $header_array[] = array('name'=>$ref_place->name . ' (Reference)', 'width'=>'18%');
    $header_array[] = array('name'=>'Similarity', 'width'=>'15%');
    // $header_array[] = array('name'=>'Q1', 'width'=>'10%');
    // $header_array[] = array('name'=>'Q3', 'width'=>'10%');
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
      $layer->rowid = $row[0];
      $layer->read_row_from_database_with_rowid($layer->rowid);
      # $layer->geoprocess->read_row_from_database_with_rowid($layer->geoprocess->rowid); happens automatically now
      echo '<tr>';
      algaeTable::writeData($layer->getHomepageLink($row[1]), False);
      algaeTable::writeData(algaeCore::getColorBlock($row[3], True, $row[2]), False);
      algaeTable::writeData($layer->geoprocess->data_distribution->name);
      
      algaeTable::writeData($layer->getFormattedDisplayValue($row[6]), False);
      algaeTable::writeData($layer->getFormattedDisplayValue($row[5]), False);
      
      //
      // ----- calc similarity
      //
      $similarity = null;
      if ($row[4] == 'Categorical')
      {
        if ($row[5] == $row[6])
        {
          $similarity = 1;
        }
        else 
        {
          $similarity = 0;
        }
      }
      else 
      {
        if ($layer->data_min != null)
        {
          $sig_range = abs($row[5] - $row[6]);
          $data_range = null;
          if ( ($layer->sig_lower_cutoff != null) && ($layer->sig_upper_cutoff != null) )
          {
            $data_range = $layer->sig_upper_cutoff - $layer->sig_lower_cutoff;
          }
          else 
          {
            $data_range = $layer->data_q3 - $layer->data_q1;
          }
          $similarity = 0;
          if ($sig_range < $data_range)
          {
            $similarity = 1 - ($sig_range / $data_range);
          }
        }
        else 
        {
          if ($row[4] != 'Categorical')
          {
            $app->errorMessage('Missing data_min for ' . $row[1] . '.');
          }
          $similarity = '';
        }
      }
      algaeTable::writeData(algaeCore::getFormattedNumber($similarity, 2));
      // algaeTable::writeData($layer->data_q1);
      // algaeTable::writeData($layer->data_q3);
      
      echo '</tr>';
    }
    algaeTable::end();
  }
  
  public function reportPlaceSignature($place)
  // --------------------------------------------------------------------------
  {
    global $app;
    $sql = "SELECT rp.layer_rowid_fk, rp.name, rp.data_group, rp.data_group_color, rp.data_type, rp.ref_val, pl.place_val,
              rp.data_min, rp.data_max, rp.data_mean, rp.data_stddev, rp.data_q1, rp.data_median, rp.data_q3
            FROM
            (
              SELECT l.rowid AS layer_rowid_fk, gp.name, dg.name AS data_group, dg.html_color AS data_group_color, 
                dt.name AS data_type, s.numeric_val AS ref_val,
                l.data_min, l.data_max, l.data_mean, l.data_stddev, l.data_q1, l.data_median, l.data_q3
              FROM sp.signature s
              INNER JOIN sp.layer l ON s.layer_rowid_fk = l.rowid
              INNER JOIN sp.geoprocess gp ON l.geoprocess_rowid_fk = gp.rowid
              INNER JOIN ref.data_group dg ON gp.data_group_rowid_fk = dg.rowid
              INNER JOIN ref.data_type dt ON gp.data_type_rowid_fk = dt.rowid
              WHERE place_rowid_fk = $1 AND l.resolution_rowid_fk = (SELECT rowid FROM ref.resolution WHERE name = $3)
            ) rp
            LEFT OUTER JOIN
            (
              SELECT l.rowid AS layer_rowid_fk, s.numeric_val AS place_val
              FROM sp.signature s
              INNER JOIN sp.layer l ON s.layer_rowid_fk = l.rowid
              WHERE place_rowid_fk = $2
            ) pl ON pl.layer_rowid_fk = rp.layer_rowid_fk";
    //
    // ----- 
    //
    $project_rowid_fk = $app->getCurrentProjectRowid(False);
    if ($project_rowid_fk != null)
    {
      $study_area = new slateStudyArea();
      $study_area->readRowFromDatabaseWithProjectRowid($project_rowid_fk);
      if (($study_area->rowid != null) && ($study_area->rowid > 0))
      {
        if ( ($study_area->place->rowid != null) && ($study_area->place->rowid > 0) )
        {
          // echo $this->getCalcSignatureLink($place->rowid);
          $parms = array($study_area->place->rowid, $place->rowid, $app->getResolutionName());
          $data = algaeDB::getArray($sql, $parms);
          if (($data != null) && (count($data) > 0))
          {
            $this->displayPlaceSignature($study_area->place, $place, $data);
          }
        }
        else 
        {
          algaeApp::errorMessage('Define a reference place as part of the Study Area.');
        }
      }
      else 
      {
        algaeApp::errorMessage('Study Area not defined.');
      }
    }
    else 
    {
      algaeApp::errorMessage('Project not defined.');
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