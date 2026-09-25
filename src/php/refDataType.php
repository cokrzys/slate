<?php

/**

  slate | Support for data type and the ref.data_type table.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refDataType extends algaeTblReferenceBase
{
  
  public $min_value;
  public $max_value;
  public $nodata_value;
  public $size_bytes;
  
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
    $this->table_name = 'ref.data_type';
    $this->homepage = 'data_type.php';
    $this->editpage = 'edit_data_type.php';
    $this->itemName = 'Data Type';
    $this->min_value = null;
    $this->max_value = null;
    $this->nodata_value = null;
    $this->size_bytes = null;
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
    algaeTable::writeTwoColumns('Min', algaeForm::inputText($this->get_control_id('min_value'),
      $this->min_value, 20), False);
    algaeTable::writeTwoColumns('Max', algaeForm::inputText($this->get_control_id('max_value'),
      $this->max_value, 20), False);
    algaeTable::writeTwoColumns('NoData', algaeForm::inputText($this->get_control_id('nodata_value'),
      $this->nodata_value, 20), False);
    algaeTable::writeTwoColumns('Size', algaeForm::inputText($this->get_control_id('size_bytes'),
      $this->size_bytes, 20) . $app->getDetailString('bytes'), False);
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
    algaeTable::writeTwoColumns('Min', $this->min_value);
    algaeTable::writeTwoColumns('Max', $this->max_value);
    algaeTable::writeTwoColumns('NoData', $this->nodata_value);
    algaeTable::writeTwoColumns('Size (bytes)', $this->size_bytes);
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
      array('Min', '10%'),
      array('Max', '8%'),
      array('NoData', '8%'),
      array('Size (bytes)', '8%')
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
    algaeTable::writeData($this->min_value);
    algaeTable::writeData($this->max_value);
    algaeTable::writeData($this->nodata_value);
    algaeTable::writeData($this->size_bytes);
  }
  
  /**
   * TODO: This could become generic and go in a base class.
   * @return refDataType[]
   */
  public static function getArrayOfObjects()
  // --------------------------------------------------------------------------
  {
    $ret_array = array();
    $o = new refDataType();
    $sql = $o->get_sql(true);
    $data = algaeDB::getArray($sql, array());
    if ( ($data != null) && (count($data) > 0) )
    {
      foreach ($data as $row)
      {
        $new_o = new refDataType();
        $new_o->read_row_from_database_with_rowid($row[0]);
        if ($new_o->name != null)
        {
          $ret_array[] = $new_o;
        }
      }
    }
    return $ret_array;
  }
  
  
}