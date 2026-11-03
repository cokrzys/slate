<?php

/**

 slate | Class to handle the setup and running of a batch of geoprocesses.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate
 
*/

class slateBatchRunGeoprocesses
{
  
  const WHOLE_PROJECT = 'Everything for the Project';
  const SEQUENCE_RANGE = 'Range of Sequence Numbers';
  
  public $choices;
  public $run_type;
  public $parameters;
  public $resolution;
  public $max_to_run;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    $this->choices = array();
    $this->choices[] = slateBatchRunGeoprocesses::WHOLE_PROJECT;
    $this->choices[] = 'List of Sequence Numbers';
    $this->choices[] = slateBatchRunGeoprocesses::SEQUENCE_RANGE;
    $parameters_prefix = 'GeoprocessesBatchRun';
    $this->run_type = algaeForm::getDefaultValue('run_type', null, $parameters_prefix . 'Type');
    $this->parameters = algaeForm::getDefaultValue('parameters', null, $parameters_prefix . 'Parameters');
    $this->resolution = new refResolution();
    $this->resolution->rowid = algaeForm::getDefaultValue('resolution_rowid_fk', null, $parameters_prefix . 'ResolutionRowid');
    if ($this->resolution->rowid != null)
    {
      $this->resolution->read_row_from_database_with_rowid($this->resolution->rowid);
    }
    $this->max_to_run = algaeForm::getDefaultValue('max_to_run', null, $parameters_prefix . 'MaxToRun');
  }
  
  /**
   * Page setup to edit or run the batch process.
   */
  public function showPage()
  // --------------------------------------------------------------------------
  {
    global $app;
    $running = false;
    if (isset($_POST['submit']))
    {
      $app->successMessage('Batch run parameters saved.');
    }
    elseif (isset($_POST['save_and_run']))
    {
      $this->setupRun();
      $running = true;
    }
    if (! $running)
    {
      $this->showForm();
    }
  }
  
  /**
   * Show form to edit process.
   */
  protected function showForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    algaeForm::startSingleTab('Batch Setup');
    $f = new algaeForm();
    $f->startForm(algaeForm::getDefaultToken($this));
    
    algaeTable::start('formTable', 'algae_form_table', '');
    algaeTable::writeHeader(array(), False);
    
    
    algaeTable::writeTwoColumns('Run', algaeForm::selectWithArray('run_type', $this->run_type, $this->choices, algaeForm::REQUIRED, 300), False);
    echo '<tr>';
    echo '<td colspan=2>', $app->getDetailString('The current project is ' . $app->getCurrentProjectName() . '.', False), '</td>';
    echo '</tr>';
    algaeTable::writeTwoColumns('Resolution', algaeForm::selectWithTableAndFieldWithRowid($this->resolution->table_name, 
      'name', 'resolution_rowid_fk', $this->resolution->name, True), False);
    algaeTable::writeTwoColumns('Parameters', algaeForm::inputText('parameters', $this->parameters, 50), False);
    echo '<tr>';
    echo '<td colspan=2>', $app->getDetailString('For a range of sequence numbers this is a simple range with no spaces.  For example 100-105.', False), '</td>';
    echo '</tr>';
    algaeTable::writeTwoColumns('Maximum to Run', algaeForm::inputText('max_to_run', $this->max_to_run, 10), False);
    
    
    algaeTable::end();
    echo '<p />';
    $f->submitButton('Save', False, 'submit');
    $f->submitButton('Save and Run', True, 'save_and_run');
    echo '</form>';
    algaeForm::endSingleTab();
  }
  
  /**
   * Setup batch process run.
   */
  protected function setupRun()
  // --------------------------------------------------------------------------
  {
    global $app;
    $parameters = array();
    $ok_to_run = False;
    algaeForm::startSingleTab('GeoProcesses Batch Run');
    
    $p = new slateProject();
    $p->rowid = $app->getCurrentProjectRowid();
    $p->read_row_from_database_with_rowid($p->rowid);
    
    //
    // ----- send by default since every type of run will use it
    //
    $parameters['--project_rowid_fk'] = $p->rowid;
    
    if ($this->run_type == slateBatchRunGeoprocesses::WHOLE_PROJECT)
    {
      echo 'Running everything for project ', $p->name, ' at ', $this->resolution->name, ' resolution.<p />';
      $ok_to_run = True;
    }
    elseif ($this->run_type == slateBatchRunGeoprocesses::SEQUENCE_RANGE)
    {
      $this->parameters = trim($this->parameters);
      if (strlen($this->parameters) > 0)
      {
        $parts = explode('-', $this->parameters);
        if (count($parts) == 2)
        {
          $where = "'" . 'sequence >= ' . $parts[0] . ' and sequence <= ' . $parts[1] . "'";
          $parameters['--where'] = $where;
        }
        $ok_to_run = True;
      }
      else 
      {
        $app->errorMessage('Sequence parameters not specified.');
      }
    }
    else 
    {
      $app->errorMessage($this->run_type . ' not supported.');
    }
    //
    //
    //
    if ($ok_to_run)
    {
      $process = new algaeTblCoreProcess();
      $process->application = algaeFile::removeExtension($app->config->run_geoprocesses_app);
      $process->logfile_root = algaeCore::getFullPath($p->folder, 'batch/');
      $process->starting_url = 'edit_geoprocesses_batch.php';
      $process->result_url = $app->config->process_status_page;
      if ($process->createProcess())
      {
        echo 'Created process ', $process->rowid, '.<p />';
        $parameters['--resolution_rowid_fk'] = $this->resolution->rowid;
        $parameters['--process_rowid_fk'] = $process->rowid;
        if ($this->max_to_run > 0)
        {
          $parameters['--max_to_run'] = $this->max_to_run;
        }
        
        echo $process->checkStatusButton();
        
        $command = $app->getPythonAppsPath('rungeoprocesses.py');
        
        foreach ($parameters as $name => $value) 
        {
          $command .= ' ' . $name . ' ' . $value;
        }
        
        $process->startInBackground($command, $app->config->process_status_page);
        
      }
    }
    algaeForm::endSingleTab();
  }
  
}




