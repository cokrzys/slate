<?php

/**

  slate | Support for resolutions and the ref.resolution table.
    
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refResolution extends algaeTblReferenceBase
{
  
  public $folder;
  public $cell_size_x;
  public $cell_size_y;
  
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
    $this->table_name = 'ref.resolution';
    $this->folder = null;
    $this->homepage = 'resolution.php';
    $this->editpage = 'edit_resolution.php';
    $this->itemName = 'Resolution';
    $this->folder = null;
    $this->cell_size_x = null;
    $this->cell_size_y = null;
  }
  
  /**
   * Add derived fields to form.
   * {@inheritDoc}
   * @see algaeTblBase::addDerivedFieldsToForm()
   */
  protected function addDerivedFieldsToForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    parent::addDerivedFieldsToForm();
    algaeTable::writeTwoColumns('Folder', algaeForm::inputText($this->get_control_id('folder'), 
      $this->folder, 20, algaeForm::REQUIRED), False);
    algaeTable::writeTwoColumns('Cell Size X', algaeForm::inputText($this->get_control_id('cell_size_x'),
      $this->cell_size_x, 20, algaeForm::REQUIRED) . $app->getDetailString('meters'), False);
    algaeTable::writeTwoColumns('Cell Size Y', algaeForm::inputText($this->get_control_id('cell_size_y'),
      $this->cell_size_y, 20, algaeForm::REQUIRED) . $app->getDetailString('meters'), False);
  }
  
  /**
   * Add derived fields to overall details table.
   * {@inheritDoc}
   * @see algaeTblBase::addDerivedFieldsToOverallDetails()
   */
  protected function addDerivedFieldsToOverallDetails()
  // --------------------------------------------------------------------------
  {
    parent::addDerivedFieldsToOverallDetails();
    algaeTable::writeTwoColumns('Folder', $this->folder);
    algaeTable::writeTwoColumns('Cell Size X', $this->cell_size_x);
    algaeTable::writeTwoColumns('Cell Size Y', $this->cell_size_y);
  }
  
  /**
   * Write the header for the records table.
   */
  protected function writeRecordsTableHeader()
  // --------------------------------------------------------------------------
  {
    $header_array = array(
      array('Action', '10%'),
      array('Rowid', '7%'),
      array('Name', '20%'),
      array('Folder', '10%'),
      array('Cell Size X', '8%'),
      array('Cell Size Y', '8%'),
      array('Status', '8%')
    );
    algaeTable::writeHeader($header_array, True);
  }
  
  /**
   * Write the record to a table.
   */
  protected function writeRecordToTable()
  // --------------------------------------------------------------------------
  {
    algaeTable::writeData($this->getActionLinks(), False);
    algaeTable::writeData($this->rowid);
    algaeTable::writeData($this->getHomepageLink(), False);
    algaeTable::writeData($this->folder);
    algaeTable::writeData($this->cell_size_x);
    algaeTable::writeData($this->cell_size_y);
    algaeTable::writeData($this->record_status->name);
  }
  
  protected function getOutputTypeBytes($output_types_array, $name)
  // --------------------------------------------------------------------------
  {
    foreach ($output_types_array as $ot)
    {
      if ($ot->name == $name)
      {
        return $ot->size_bytes;
      }
    }
    return null;
  }
  
  public static function reportFileSizeEstimates($study_area)
  // --------------------------------------------------------------------------
  {
    global $app;
    $num_files = 500;
    $data_types_to_estimate = array('Byte', 'Int16', 'Int32', 'Float32', 'Float64');
    $resolution = new refResolution();
    $sql = $resolution->get_sql();
    $sql .= " WHERE $resolution->table_name.cell_size_x IS NOT NULL";
    //
    // ----- read the data
    //
    $data = algaeDB::getArray($sql, array());
    if (count($data) > 0)
    {
      echo 'Estimated GeoTIFF file sizes for different resolutions and data types.<p />';
      //
      //
      //
      $output_types_array = refOutputType::getArrayOfObjects();
      //
      // ----- initial the table
      //
      $tableId = 'resolutionFilesizeEstimatesTable';
      algaeTable::initTablesorterJavascript($tableId, '[[0,0]]');
      algaeTable::start($tableId, 'tablesorter', 'width:100%;');
      //
      // ----- setup associative array with header details
      //
      $header_array = array();
      $header_array[] = array('name'=>'Resolution', 'width'=>'15%');
      $header_array[] = array('name'=>'Num Cells', 'width'=>'15%');
      foreach ($data_types_to_estimate as $item)
      {
        $header_array[] = array('name'=>$item, 'width'=>'15%');
      }
      //
      // ----- write headers
      //
      algaeTable::writeHeaderWithAssociativeArray($header_array);
      //
      // ----- loop through the results
      //
      foreach ($data as $row)
      {
        $resolution->init();
        $resolution->read_row_from_database_with_rowid($row[0]);
        $num_cells = $study_area->getNumCells($resolution->cell_size_x);
        echo '<tr>';
        # $resolution->cell_size_x parameter used to set a data sort value
        algaeTable::writeData($resolution->name, True, '', '', '', $resolution->cell_size_x);
        algaeTable::writeData(algaeCore::getFormattedNumber($num_cells, 0));
        
        foreach ($data_types_to_estimate as $item)
        {
          $output_type_bytes = $resolution->getOutputTypeBytes($output_types_array, $item);
          if ($output_type_bytes != null)
          {
            $size_bytes = $num_cells * $output_type_bytes;
            algaeTable::writeData(algaeFile::getHumanFilesize($size_bytes, 2) . $app->settings->menuSeparator . 
              algaeFile::getHumanFilesize($size_bytes * $num_files, 0), False);
          }
          else
          {
            algaeTable::writeData('-');
          }
        }
        
        echo '</tr>';
      }
      algaeTable::end();
      echo '<div class="footnote">';
      echo 'Estimated Size = Num Cells * Data Type Bytes<p />';
      echo 'One File ', $app->settings->menuSeparator, $num_files, ' Files<p />';
      echo '</div>';
    }
    else
    {
      echo 'Nothing to report.<p />';
    }
  }
  
}
