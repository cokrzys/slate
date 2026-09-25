<?php

/**

  slate | Support for units and the ref.units table.
  
  Units are used for reporting purposes and conversions in seleted cases.
  For example Degrees Celsius to/from Degrees Farenheit.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refUnits extends algaeTblReferenceBaseV2
{
  
  public $abbreviation;
  
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
    $this->table_name = 'ref.units';
    $this->homepage = 'units.php';
    $this->editpage = 'edit_units.php';
    $this->itemName = 'Units';
    $this->itemNamePlural = 'Units';
    $this->abbreviation = null;
  }
  
  /**
   * Add derived fields to form.
   * {@inheritDoc}
   * @see algaeTblBase::addDerivedFieldsToForm()
   */
  protected function addDerivedFieldsToForm()
  // --------------------------------------------------------------------------
  {
    parent::addDerivedFieldsToForm();
    algaeTable::writeTwoColumns('Abbreviation', algaeForm::inputText($this->get_control_id('abbreviation'), 
      $this->abbreviation, 20, algaeForm::REQUIRED), False);
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
    algaeTable::writeTwoColumns('Abbreviation', $this->abbreviation);
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
      array('Abbreviation', '10%'),
      array('Color', '8%'),
      array('Sort Order', '8%'),
      array('Status', '8%'),
      array('Description', '30%')
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
    algaeTable::writeData($this->abbreviation);
    algaeTable::writeData(algaeCore::getColorBlock($this->html_color, True), False);
    algaeTable::writeData($this->sort_order);
    algaeTable::writeData($this->record_status->name);
    algaeTable::writeData(algaeCore::getStringWithLinks($this->description), False);
  }
  
}
