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
          $u = new algaeFile();
          // TODO: Better way other than just adding a backslash.
          $u->target_dir = $this->project->getDirectory(slateProject::VECTOR_DATA_DIRECTORY) . '/';
          if ($u->uploadMultiple('components') > 0)
          {
            if (strlen($this->source_filename) > 0)
            {
              $this->showForm();
            }
          }
        }
      }
    }
  }
  
  public function showForm()
  // --------------------------------------------------------------------------
  {
    /*
    algaeForm::startTabs(array(
      array('#raster_tab', 'Raster'),
      array('#vector_tab', 'Vector'),
      array('#other_tab', 'Other')
    ));
    $this->showTab('raster_tab', slateSourceData::RASTER);
    $this->showTab('vector_tab', slateSourceData::VECTOR);
    $this->showTab('other_tab', slateSourceData::OTHER);
    algaeform::endTabs();
    */
    global $app;
    algaeForm::startSingleTab('Source Data');
    echo $app->getPageLink('select_data_to_upload.php', 'Upload', algaeAccess::ROLE_WRITE, $app->config->app_name);
    echo $app->getPageLink('link_to_data.php', 'Link', algaeAccess::ROLE_WRITE, $app->config->app_name, '');
    echo '<p />';
    algaeForm::endSingleTab();
  }
  
}




