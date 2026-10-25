<?php

/**

  slate | Class and sp.class support.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateClass extends algaeTblBase
{
  
  public $layer;
  public $raster_value;
  public $num_values;
  public $code;
  public $html_color;
  public $description;
  
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
    $this->table_name = 'sp.class';
    $this->layer = new slateLayer();
    $this->raster_value = null;
    $this->num_values = null;
    $this->code = null;
    $this->html_color = null;
    $this->description = null;
  }
 
  /**
   * Get links to act on the item.
   * @return string  HTML string with the links.
   * {@inheritDoc}
   * @see algaeTblBase::getActionLinks()
   */
  public function getActionLinks($openInNewTab=False)
  // --------------------------------------------------------------------------
  {
    $html = '';
    return $html;
  }
  
  public function readRowFromDatabaseWithLayerAndValue($layer_rowid_fk, $val)
  // --------------------------------------------------------------------------
  {
    $sql = $this->getSQL();
    $sql .= " WHERE {$this->table_name}.layer_rowid_fk = $1 AND {$this->table_name}.raster_value = $2";
    $this->readRowFromDatabaseWithSQL($sql, array($layer_rowid_fk, intval($val)));
    if ($this->rowid > 0)
    {
      return True;
    }
    return False;
  }
  
  /**
   * Tabular report of records.
   * {@inheritDoc}
   * @see algaeTblBase::reportRecords()
   */
  public function reportRecords($tableId = 'slateClassesTable', $whereClause = '', $maxRecords = 10000)
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql(True);
    $sql .= $whereClause;
    $data = algaeDB::getArray($sql, array());
    if (count($data) > 0)
    {
      $count_sql = "SELECT SUM(num_values) FROM $this->table_name " . $whereClause;
      $total_num_values = algaeDB::getScalarInteger($count_sql, array(), null);
      //
      // ----- initial the table
      //
      $tableId = $this->getDefaultRecordsTableId($tableId);
      algaeTable::initTablesorterJavascript($tableId, '[[0,0]]');
      algaeTable::start($tableId, 'tablesorter', 'width:100%;');
      //
      // ----- table header
      //
      $header_array = array(
        array('Value', '10%', False),
        array('Code', '15%'),
        array('Color', '15%'),
        array('Num Values', '10%'),
        array('Pct of Total', '10%'),
        array('Description', '40%')
      );
      algaeTable::writeHeader($header_array, True);
      //
      // ----- loop through the results
      //
      foreach ($data as $row)
      {
        $o = new slateClass();
        $o->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        algaeTable::writeData($o->raster_value);
        algaeTable::writeData($o->code);
        algaeTable::writeData(algaeCore::getColorBlock($o->html_color, True), False);
        algaeTable::writeData(algaeCore::getFormattedNumber($o->num_values, 0, null));
        if ($total_num_values > 0)
        {
          algaeTable::writeData(algaeCore::getFormattedPercentage(($o->num_values / $total_num_values) * 100, 2, null));
        }
        else
        {
          algaeTable::writeData('');
        }
        algaeTable::writeData($o->description);
        echo '</tr>';
      }
      algaeTable::end();
    }
  }
  
  /**
   * Report records for the current project.
   */
  public function reportRecordsForLayer($layer_rowid_fk)
  // --------------------------------------------------------------------------
  {
    $this->reportRecords('slateClassesTable', " WHERE $this->table_name.layer_rowid_fk = " . $layer_rowid_fk);
  }
  
}






