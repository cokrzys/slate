<?php

/**

  slate | Support for file formats and the ref.file_format table.
    
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class refFileFormat extends algaeTblBase
{
  
  public $record_status;
  public $extension;
  public $name;
  public $description;
  public $file_group;
  
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
    $this->table_name = 'ref.file_format';
    $this->homepage = 'file_format.php';
    $this->editpage = 'edit_file_format.php';
    $this->itemName = 'File Format';
    $this->record_status = new algaeTblRecordStatus();
    $this->extension = null;
    $this->description = null;
    $this->file_group = new refFileGroup();
  }
  
  public function read_row_from_database_with_extension($extension)
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql();
    $sql .= " WHERE $this->table_name.extension = $1";
    return $this->read_row_from_database_with_sql($sql, array($extension));
  }
  
  /**
   * Process a form that's been submitted.
   */
  public function processForm()
  // --------------------------------------------------------------------------
  {
    if (isset($_POST['submit']))
    {
      if (algaeForm::validTokens(algaeForm::getDefaultToken($this)))
      {
        $this->post_control_data();
        if (isset($_REQUEST['rowid']))
        {
          if ($this->update())
          {
            algaeApp::successMessage($this->name . ' successfully updated.');
            return True;
          }
          else
          {
            algaeApp::errorMessage('Problem updating ' . $this->name . '.');
          }
        }
        else
        {
          if ($this->insert())
          {
            algaeApp::successMessage($this->name . ' successfully added.');
            return True;
          }
          else
          {
            algaeApp::errorMessage('Problem adding ' . $this->name . '.');
          }
        }
      }
    }
    return False;
  }
  
  protected function showOverviewTab($form)
  // --------------------------------------------------------------------------
  {
    echo '<div id="overview_tab">';
    if ($this->rowid > 0)
    {
      echo '<input type="hidden" name="rowid" value="', $this->rowid, '" />';
    }
    //
    // ----- table to keep items aligned
    //
    algaeTable::start('formTable', 'algae_form_table', '');
    algaeTable::writeHeader(array(), False);
    //
    // ----- extension
    //
    algaeTable::writeTwoColumns('Extension', algaeForm::inputText($this->get_control_id('extension'),
      $this->extension, 10, algaeForm::REQUIRED), False);
    //
    // ----- name
    //
    algaeTable::writeTwoColumns('Name', algaeForm::inputText($this->get_control_id('name'),
      $this->name, 50, algaeForm::REQUIRED), False);
    //
    // ----- file group
    //
    algaeTable::writeTwoColumns('File Group',
      $this->file_group->getControl($this, 'file_group_rowid_fk'), False);
    //
    // ----- description
    //
    algaeTable::writeTwoColumns('Description', '', False);
    echo '<tr><td colspan="2">';
    echo '<textarea name="' . $this->get_control_id('description') . '" cols="83" rows="7">',
    algaeCore::toHtml($this->description), '</textarea><p />';
    echo '</td></tr>';
    //
    // ----- record status
    //
    algaeTable::writeTwoColumns('Status', $this->record_status->getControl($this), False);
    algaeTable::end();
    $form->submitButton('Save', False);
    echo '</div>';
  }
  
  /**
   * Show form to edit a record.
   */
  public function showForm()
  // --------------------------------------------------------------------------
  {
    $f = new algaeForm();
    $f->startForm(algaeForm::getDefaultToken($this));
    //
    // ----- get data if editing
    //
    if (isset($_REQUEST['rowid']))
    {
      $this->read_row_from_database_with_rowid($_REQUEST['rowid']);
    }
    algaeForm::startTabs(array(
      array('#overview_tab', 'File Format'),
      array('#formats_tab', 'Existing Formats ')
    ));
    //
    // ----- overview_tab
    //
    $this->showOverviewTab($f);
    //
    // ----- formats_tab
    //
    echo '<div id="formats_tab">';
    $this->report_records();
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs('tabs');
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
  }
  
  protected function report_records($tableId = 'sourceData', $where_clause = null)
  // --------------------------------------------------------------------------
  {
    $sql = $this->get_sql(true);
    //
    // ----- read the data
    //
    $data = algaeDB::getArray($sql, array());
    if (count($data) > 0)
    {
      //
      // ----- initial the table
      //
      algaeTable::initTablesorterJavascript($tableId, '[[1,0]]');
      algaeTable::start($tableId, 'tablesorter', 'width:80%;');
      //
      // ----- setup associative array with header details
      //
      $header_array = array();
      $header_array[] = array('name'=>'Action', 'width'=>'10%', 'stats'=>False, 'sorter'=>'false');
      $header_array[] = array('name'=>'Extension', 'width'=>'15%');
      $header_array[] = array('name'=>'Name', 'width'=>'15%');
      $header_array[] = array('name'=>'Group', 'width'=>'15%');
      $header_array[] = array('name'=>'Description', 'width'=>'55%');
      //
      // ----- write headers
      //
      algaeTable::writeHeaderWithAssociativeArray($header_array);
      //
      // ----- loop through the results
      //
      $o = new refFileFormat();
      foreach ($data as $row)
      {
        $o->init();
        $o->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        algaeTable::writeData($o->getActionLinks(), False);
        algaeTable::writeData($o->getHomepageLink($o->extension), False);
        algaeTable::writeData($o->name);
        algaeTable::writeData($o->file_group->getHomepageLink(), False);
        algaeTable::writeData(algaeCore::getStringWithLinks($o->description), False);
        echo '</tr>';
      }
      algaeTable::end();
    }
    else
    {
      echo 'Nothing to report.<p />';
    }
  }
  
  /**
   * Report the overall details for the field.
   */
  protected function reportOverallDetails()
  // --------------------------------------------------------------------------
  {
    echo $this->getActionLinks(), '<p />';
    algaeTable::start($this->itemName . 'DetailsTable', 'algae_table', 'width:85%');
    algaeTable::writeHeader(array(), False);
    algaeTable::writeTwoColumns('Extension', '<b>' . $this->extension . '</b>', False);
    algaeTable::writeTwoColumns('Name', $this->name);
    algaeTable::writeTwoColumns('File Group', $this->file_group->getHomepageLink(), False);
    algaeTable::writeTwoColumns('Description', algaeCore::getStringWithLinks($this->description), False);
    algaeTable::writeTwoColumns('Status', algaeCore::getColorBlock($this->record_status->html_color, True, $this->record_status->name), False);
    algaeTable::writeTwoColumns('Added', $this->timestamp_loaded_utc);
    algaeTable::writeTwoColumns('Modified', $this->timestamp_modified_utc);
    algaeTable::writeTwoColumns('Rowid', $this->rowid);
    algaeTable::end();
  }
  
  public function reportDetails()
  // --------------------------------------------------------------------------
  {
    algaeForm::startTabs(array(
      array('#overview_tab', 'Format'),
      array('#formats_tab', 'Formats ')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- formats_tab
    //
    echo '<div id="formats_tab">';
    $this->report_records();
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs('tabs');
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
  }
  
  public function getFormatRowidForFilename($filename)
  // --------------------------------------------------------------------------
  {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $sql = "SELECT rowid FROM $this->table_name WHERE LOWER(extension) = $1";
    return algaeDB::getScalarInteger($sql, array($ext), null);
  }
  
}
