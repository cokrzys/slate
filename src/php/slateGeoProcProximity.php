<?php

/**

  slate | Proximity GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcProximity extends slateGeoprocess
{
  
  CONST VECTOR_SOURCE = 1;
  CONST RASTER_SOURCE = 2;
  CONST VECTOR_COMMAND = 'proximity_from_vector.sh';
  CONST RASTER_COMMAND = 'proximity_from_raster.sh';
  
  public $source;
  public $source_file;
  public $max_distance;
  public $output_prefix;
  public $filter;
  public $invert;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->init();
  }
  
  public function init()
  // --------------------------------------------------------------------------
  {
    parent::init();
    $this->source_file = new slateSourceFile();
    $this->command = 'proximity_from_vector.sh';
    $this->name = 'Proximity';
    $this->mainTabName = 'Proximity';
    $this->showBatchParametersTab = True;
    $this->data_type->name = 'Float32';
    $this->max_distance = null;
    $this->output_prefix = null;
    $this->filter = null;
    $this->invert = False;
    $this->source = slateGeoProcProximity::VECTOR_SOURCE;
    $this->command = slateGeoProcProximity::VECTOR_COMMAND;
  }
  
  protected function postReadFromDatabase()
  // --------------------------------------------------------------------------
  {
    if ($this->command == slateGeoProcProximity::VECTOR_COMMAND)
    {
      $this->source = slateGeoProcProximity::VECTOR_SOURCE;
    }
    elseif ($this->command == slateGeoProcProximity::RASTER_COMMAND)
    {
      $this->source = slateGeoProcProximity::RASTER_SOURCE;
    }
  }
  
  /**
   * Decode parameters.
   * {@inheritDoc}
   * @see slateGeoprocess::decodeParameters()
   */
  protected function decodeParameters()
  // --------------------------------------------------------------------------
  {
    $a = json_decode($this->parameters, true);
    if (isset($a))
    {
      if (array_key_exists('source_file_rowid_fk', $a)) 
      { 
        $this->source_file->read_row_from_database_with_rowid($a['source_file_rowid_fk']);
      }
      if (array_key_exists('output_prefix', $a)) { $this->output_prefix = $a['output_prefix']; }
      if (array_key_exists('max_distance', $a)) { $this->max_distance = $a['max_distance']; }
      if (array_key_exists('invert', $a)) { $this->invert = algaeCore::getBoolean($a['invert']); }
      if (array_key_exists('filter', $a)) { $this->filter = $a['filter']; }
    }
  }
  
  /**
   * Add derived fields to form.
   * {@inheritDoc}
   * @see slateGeoprocess::addDerivedFieldsToForm()
   */
  protected function addDerivedFieldsToForm()
  // --------------------------------------------------------------------------
  {
    global $app;
    parent::addDerivedFieldsToForm();
    echo '<p />';
    if ($this->source == slateGeoProcProximity::VECTOR_SOURCE)
    {
      algaeTable::writeTwoColumns('Shapefile', slateSourceFile::selectShapefile('source_file_rowid_fk',
        $this->source_file->filename, True), False);
    }
    elseif ($this->source == slateGeoProcProximity::RASTER_SOURCE)
    {
      algaeTable::writeTwoColumns('Raster', slateSourceFile::selectRaster('source_file_rowid_fk',
        $this->source_file->filename, True), False);
    }
    else 
    {
      algaeApp::errorMessage('Unknown source in ' . __METHOD__);
    }
    $this->output_prefix = $this->project->getDefaultPrefix($this->output_prefix);
    algaeTable::writeTwoColumns('Output Prefix', algaeForm::inputText('output_prefix', $this->output_prefix, 40, algaeForm::REQUIRED) .
      $app->getDetailString($app->getFilenameTemplate()), False);
    algaeTable::writeTwoColumns('Max Distance', algaeForm::inputText('max_distance', $this->max_distance, 10), False);
    algaeTable::writeTwoColumns('Invert', algaeForm::checkbox('invert', 'invert', array(), $this->invert), False);
    algaeTable::writeTwoColumns('Filter', algaeForm::inputText('filter', $this->filter, 100), False);
  }
  
  /**
   * Process derived variables.
   * {@inheritDoc}
   * @see algaeTblBase::processDerivedVariables()
   */
  protected function processDerivedVariables()
  // --------------------------------------------------------------------------
  {
    parent::processDerivedVariables();
    //
    //
    //
    $this->source_file->rowid = algaeForm::cleanInput($_POST['source_file_rowid_fk']);
    //
    // the single quotes in the filter do not need to be escaped with trim(pg_escape_string($str))
    // in the algaeForm::cleanInput() call below, seems like this is different from the paramaterized
    // newer logic vs writing in-line sql like before
    //
    $this->filter = $_POST['filter'];
    
    $a = array();
    $a['source_file_rowid_fk'] = $this->source_file->rowid;
    $a['output_prefix'] = $this->output_prefix;
    $a['max_distance'] = $this->max_distance;
    $a['invert'] = algaeCore::getTrueFalse($this->invert);
    $a['filter'] = $this->filter;
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
  protected function runBatchProcess($parent_geoprocess)
  // --------------------------------------------------------------------------
  {
    global $app;
    if (strlen($parent_geoprocess->batch_parameters) > 0)
    {
      $data = json_decode($parent_geoprocess->batch_parameters, true);
      if (count($data) > 0)
      {
        echo 'Processing ', count($data), ' item(s).<p />';
        foreach ($data as $row)
        {
          $gp = clone $parent_geoprocess;
          
          $gp->batch_parameters = null;
          $gp->process = new algaeTblCoreProcess();
          
          if (array_key_exists('name', $row)) { $gp->name = $row['name']; }
          if (array_key_exists('filename', $row)) { $gp->output_prefix = $row['filename']; }
          if (array_key_exists('filter', $row)) { $gp->filter = str_replace("'", "''", $row['filter']); } // str_replace("'", "''", $strRet)
          if (array_key_exists('notes', $row)) { $gp->description = $row['notes']; }
          $gp->sequence = 100;  // TODO: Do something better, set sequence in parsecodes.py
          
          $gp->parametersToJSONString();
          
          if ($gp->okToWrite())
          {
            echo 'Ok to write new GeoProcess ', $gp->name, '<p />';
            if ($gp->writeToDatabase())
            {
              $app->successMessage('Created a new GeoProcess for ' . $gp->name . '.');
              // $gp->project->incrementNextGeoProcessId();
            }
            else 
            {
              $app->errorMessage('Unable to create a GeoProcess for ' . $gp->name . '.');
            }
          }
        }
      }
    }
    else
    {
      $app->errorMessage('No batch parameters setup.');
    }
  }
  
}




