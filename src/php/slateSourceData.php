<?php

/**

  slate | Source data and sp.source_data support class.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateSourceData extends algaeTblBase
{
  
  public $project;
  public $name;
  public $description;
  public $record_status;
  public $data_group;
  public $data_location;
  public $folder;
  public $url;
  
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
    $this->table_name = 'sp.source_data';
    $this->homepage = 'source_data.php';
    $this->editpage = 'edit_source_data.php';
    $this->browsepage = 'browse_source_data.php';
    $this->deletepage = 'delete_source_data.php';
    $this->itemName = 'Source Data';
    $this->itemNamePlural = 'Source Data';
    $this->project = new slateProject();
    $this->name = null;
    $this->folder = null;
    $this->url = null;
    $this->description = null;
    $this->record_status = new algaeTblRecordStatus();
    $this->data_group = new refDataGroup();
    $this->data_location = new refDataLocation();
  }
  
  public function getDirectory()
  # ---------------------------------------------------------------------------
  {
    global $app;
    return $this->project->getItemDirectory(null, $app->config->source_data_folder, $this->rowid, False);
  }
  
  public function selectDataToUploadForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    $project = new slateProject();
    algaeForm::startSingleTab('Data');
    $project->rowid = $app->getCurrentProjectRowid();
    if ($project->rowid > 0)
    {
      $project->read_row_from_database_with_rowid($project->rowid);
      $f = new algaeForm();
      echo 'Upload data for project <b>', $project->name, '</b></p>';
      echo '<form action="upload_data.php?project_rowid_fk=' . $project->rowid . '" method="post" enctype="multipart/form-data">';
      echo '<input type="hidden" name="file_selector" value="True" />';
      echo '<input class="ui-button ui-widget ui-corner-all" name="components[]" type="file" multiple="" /><p />';
      $f->submitButton('Upload');
      echo '</form>';
    }
    algaeForm::endSingleTab();
  }
  
  protected function createDataset()
  // --------------------------------------------------------------------------
  {
    $this->name = 'Dataset Uploaded ' . date('d-M-Y G:i:s') . ' UTC';
    $this->record_status->set_rowid_for_active();
    $this->data_location->read_row_from_database_with_name('Upload');
    $this->data_group->read_row_from_database_with_name('Other');
    if ($this->write())
    {
      return True;
    }
    return False;
  }
  
  public function showUploadProcessor()
  // --------------------------------------------------------------------------
  {
    if ($this->project->readDetailsForProjectRowidOnTheURL())
    {
      if (isset($_POST["submit"]))
      {
        //
        // ----- if uploading show form to add database entries
        //
        if (isset($_POST['file_selector']))
        {
          if ($this->createDataset())
          {
            $u = new algaeFile();
            $u->target_dir = $this->getDirectory();
            if ($u->makeFolder($u->target_dir))
            {
              # echo 'DEBUG: target_dir = ', $u->target_dir, '<p />';
              if ($u->uploadMultiple('components', True, True) > 0)
              {
                header("Location: {$this->editpage}?rowid={$this->rowid}");
              }
            }
          }
        }
      }
    }
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
      algaeTable::start($tableId, 'tablesorter', 'width:100%;');
      //
      // ----- setup associative array with header details
      //
      $header_array = array();
      $header_array[] = array('name'=>'Action', 'width'=>'10%', 'stats'=>False, 'sorter'=>'false');
      $header_array[] = array('name'=>'Dataset', 'width'=>'30%');
      $header_array[] = array('name'=>'Data Group', 'width'=>'20%');
      $header_array[] = array('name'=>'Description', 'width'=>'40%');
      //
      // ----- write headers
      //
      algaeTable::writeHeaderWithAssociativeArray($header_array);
      //
      // ----- loop through the results
      //
      $o = new slateSourceData();
      foreach ($data as $row)
      {
        $o->init();
        $o->read_row_from_database_with_rowid($row[0]);
        echo '<tr>';
        algaeTable::writeData($o->getActionLinks(False, False), False);
        algaeTable::writeData($o->getHomepageLink(), False);
        algaeTable::writeData($o->data_group->getHomepageLink($this->name, algaeAccess::ROLE_READ, False, null, True), False);
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
  
  public function showForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    algaeForm::startSingleTab('Source Data');
    echo $app->getPageLink('select_data_to_upload.php', 'Upload', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
    // echo $app->getPageLink('link_to_data.php', 'Link (TODO)', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
    echo '<p />';
    $this->report_records();
    algaeForm::endSingleTab();
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
    // ----- name
    //
    algaeTable::writeTwoColumns('Name', algaeForm::inputText($this->get_control_id('name'),
      $this->name, 50, algaeForm::REQUIRED), False);
    //
    // ----- data group
    //
    algaeTable::writeTwoColumns('Data Group', 
      $this->data_group->getControl($this, 'data_group_rowid_fk'), False);
    //
    // ----- url
    //
    algaeTable::writeTwoColumns('Source URL', algaeForm::inputText($this->get_control_id('url'),
      $this->url, 75), False);
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
   * Report files for the process.
   */
  protected function reportFiles()
  // --------------------------------------------------------------------------
  {
    $folder = $this->getDirectory();
    $files = scandir($folder);
    if (sizeof($files) > 2)
    {
      echo sizeof($files), ' file(s) in ', $folder, '<p />';
      //
      // ----- initial the table
      //
      $tableId = 'filesTable';
      algaeTable::initTablesorterJavascript($tableId, '[[0,0]]', True, "headers: {2: {sorter:'milDate'} }");
      algaeTable::start($tableId, 'tablesorter', 'width:80%;');
      //
      // ----- table header
      //
      $header_array = array(
        array('Filename', '40%'),
        array('Size (bytes)', '20%'),
        array('Date', '20%'),
        array('Actions', '20%')
      );
      algaeTable::writeHeader($header_array, True);
      //
      // ----- loop through the results
      //
      foreach ($files as $file)
      {
        if ( ($file != '.') && ($file != '..') )
        {
          $full_filename = algaeCore::getFullPath($folder, $file);
          echo '<tr>';
          algaeTable::writeData($file);
          algaeTable::writeData(algaeCore::getFormattedNumber(filesize($full_filename), 0));
          algaeTable::writeData(date("d-M-Y G:i:s", filemtime($full_filename)), 0);
          // algaeTable::writeData($this->getFileActions($full_filename), False);
          algaeTable::writeData('-');
          echo '</tr>';
        }
      }
      algaeTable::end();
    }
    else
    {
      echo 'No file(s) in ', $folder, '<p />';
    }
  }
  
  /**
   * Show form to edit a record.
   */
  public function showEditForm()
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
      array('#overview_tab', 'Dataset'),
      array('#files_tab', 'Files ')
    ));
    //
    // ----- overview_tab
    //
    $this->showOverviewTab($f);
    //
    // ----- files_tab
    //
    echo '<div id="files_tab">';
    $this->reportFiles();
    $this->refreshFilesList();
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs('tabs');
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
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
    algaeTable::writeTwoColumns('Name', '<b>' . $this->name . '</b>', False);
    algaeTable::writeTwoColumns('Folder', $this->getDirectory());
    algaeTable::writeTwoColumns('Data Group', $this->data_group->getHomepageLink($this->data_group->name, algaeAccess::ROLE_READ, False, null, True), False);
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
      array('#overview_tab', 'Dataset'),
      array('#files_tab', 'Files ')
    ));
    //
    // ----- overview_tab
    //
    echo '<div id="overview_tab">';
    $this->reportOverallDetails();
    echo '</div>';
    //
    // ----- files_tab
    //
    echo '<div id="files_tab">';
    $this->refreshFilesList();
    slateFolder::reportFiles($this->getDirectory());
    echo '</div>';
    //
    // ----- end tabs
    //
    algaeForm::endTabs('tabs');
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
  }
  
  /**
   * https://stackoverflow.com/questions/7121479/listing-all-the-folders-subfolders-and-files-in-a-directory-using-php
   * @param string $dir
   */
  protected function scanDirectory($dir)
  // --------------------------------------------------------------------------
  {
    $ffs = scandir($dir);
    
    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);
    
    // prevent empty ordered elements
    if (count($ffs) < 1)
      return;
      
    foreach ($ffs as $ff)
    {
      if (is_dir($dir.'/'.$ff)) 
      {
        listFolderFiles($dir.'/'.$ff);
      }
      else 
      {
        $full_filename = algaeCore::getFullPath($dir, $ff);
        echo $full_filename, '<p />';
        
        $f = new slateSourceFile();
        $f->filename = $full_filename;
        $f->source_data->rowid = $this->rowid;
        $f->size_bytes = filesize($full_filename);
        $f->file_format->rowid = $f->file_format->getFormatRowidForFilename($full_filename);
        $f->write();
        
      }
    }
    echo '</ol>';
  }
  
  public function refreshFilesList()
  // --------------------------------------------------------------------------
  {
    if ($this->folder != null)
    {
      $this->scanDirectory($this->folder);
    }
    else 
    {
      $this->scanDirectory($this->getDirectory());
    }
  }
  
  protected function checkStudyAreaUse()
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT COUNT(*) AS num
            FROM sp.study_area sa
            INNER JOIN sp.source_file sf ON sa.source_file_rowid_fk = sf.rowid
            WHERE sf.source_data_rowid_fk = $1";
    $num = algaeDB::getScalarInteger($sql, array($this->rowid), 0);
    if ($num > 0)
    {
      algaeApp::errorMessage('Used in one or more study areas.  Change the study area(s) before deleting.');
    }
  }
  
  protected function checkGeoProcessUse()
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT t2.rowid
            FROM
            (
              SELECT rowid, name,
            	CASE
            	  WHEN source_file_rowid_fk IS NOT NULL THEN CAST(source_file_rowid_fk AS INTEGER)
            	  WHEN raster_file_rowid_fk IS NOT NULL THEN CAST(raster_file_rowid_fk AS INTEGER)
            	ELSE NULL
            	END AS source_file_rowid_fk
              FROM
              (
            	SELECT rowid, name,
            	  CAST(parameters::json->'source_file_rowid_fk' AS TEXT) AS source_file_rowid_fk,
            	  CAST(parameters::json->'raster_file_rowid_fk' AS TEXT) AS raster_file_rowid_fk
            	FROM sp.geoprocess
              ) t1
            ) t2
            INNER JOIN sp.source_file sf ON t2.source_file_rowid_fk = sf.rowid
            WHERE sf.source_data_rowid_fk = $1";
    $data = algaeDB::getArray($sql, array($this->rowid));
    if (count($data) > 0)
    {
      echo 'Data used by the following GeoProcesses.<p />';
      foreach ($data as $row)
      {
        $gp = new slateGeoProcess();
        $gp->read_row_from_database_with_rowid($row[0]);
        echo $gp->getHomepageLink(), '<p />';
      }
      algaeApp::errorMessage('Deleting the data will break the GeoProcesses.');
    }
  }
  
  public function showWhatsBeingDeleted()
  // --------------------------------------------------------------------------
  {
    $sf = new slateSourceFile();
    $num = algaeDB::getScalarInteger("SELECT COUNT(rowid) FROM $sf->table_name WHERE source_data_rowid_fk = $1", 
      array($this->rowid), 0);
    if ($num > 0)
    {
      echo $num, ' row(s) from ', $sf->table_name, '.<p />';
    }
    echo 'Directory ', $this->getDirectory(), '.<p />';
    
    $this->checkStudyAreaUse();
    $this->checkGeoProcessUse();    
  }
  
  public function delete()
  // --------------------------------------------------------------------------
  {
    algaeFile::deleteDirectory($this->getDirectory());
    $sf = new slateSourceFile();
    algaeDB::deleteFromTable($sf->table_name, 'source_data_rowid_fk', $this->rowid);
    parent::delete();
  }
  
}




