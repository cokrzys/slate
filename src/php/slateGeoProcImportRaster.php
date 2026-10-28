<?php

/**

  slate | Import raster GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcImportRaster extends slateGeoprocess
{
  
  public $source_file;
  public $output_prefix;
  public $legend_file;
  public $legend_from_database;
  public $resampling_method;
  public $cutoff_units;
  public $lower_cutoff;
  public $upper_cutoff;
  public $output_type;
  
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
    $this->command = 'import_raster.sh';
    $this->name = 'Import Raster';
    $this->mainTabName = 'Import Raster';
    $this->source_file = new slateSourceFile();
    $this->legend_file = new slateSourceFile();
    $this->legend_from_database = null;
    $this->output_prefix = null;
    $this->resampling_method = 'average';
    $this->cutoff_units = 'Percentile';  # can be Percentile or Absolute
    $this->lower_cutoff = 10;
    $this->upper_cutoff = 90;
    // $this->output_type = 'Float32';
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
    if ($a != null)
    {
      if (array_key_exists('raster_file_rowid_fk', $a)) 
      { 
        $this->input_raster->rowid = $a['raster_file_rowid_fk'];
        $this->input_raster->readRowFromDatabaseWithRowid($this->input_raster->rowid);
      }
      if (array_key_exists('legend_file_rowid_fk', $a))
      {
        $this->legend_file->rowid = $a['legend_file_rowid_fk'];
        $this->legend_file->readRowFromDatabaseWithRowid($this->legend_file->rowid);
      }
      $this->output_prefix = $a['output_prefix'];
      $this->resampling_method = $a['resampling_method'];
      if (array_key_exists('cutoff_units', $a)) { $this->cutoff_units = $a['cutoff_units']; }
      if (array_key_exists('lower_cutoff', $a)) { $this->lower_cutoff = $a['lower_cutoff']; }
      if (array_key_exists('upper_cutoff', $a)) { $this->upper_cutoff = $a['upper_cutoff']; }
      if (array_key_exists('legend_from_database', $a)) { $this->legend_from_database = $a['legend_from_database']; }
    }
    // if (array_key_exists('output_type', $a)) { $this->output_type = $a['output_type']; }
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
    
    
    if ($this->output_prefix == null)
    {
      $this->output_prefix = $this->project->abbreviation . '_';
    }
    
    algaeTable::writeTwoColumns('Raster', 
      
      
      slateSourceFile::selectRaster('source_file_rowid_fk',
        $this->source_file->filename, True), False);
    
    algaeTable::writeTwoColumns('Output Prefix', algaeForm::inputText('output_prefix', $this->output_prefix, 40, algaeForm::REQUIRED) . 
      $app->getDetailString($app->getFilenameTemplate()), False);
    
    // algaeTable::writeTwoColumns('Output Type', algaeForm::selectWithTableAndField('ref.output_type', 'name', 'output_type', $this->output_type), False);
    
    /*
    algaeTable::writeTwoColumns('Legend File', 
      algaeForm::selectWithSQL($sql, 'legend_file_rowid_fk', $this->legend_file->filename, False, [], 400), False);
    */
    
    algaeTable::writeTwoColumns('Legend',
      algaeForm::selectWithTableAndFieldWithRowid('sp.legend', 'name', 'legend_from_database', $this->legend_from_database), False);
    
    $resampling_methods = array('average', 'bilinear', 'cubic', 'cubicspline', 'lanczos', 'max', 'med', 'min', 'mode', 'near', 'q1', 'q3', 'rms', 'sum');
    algaeTable::writeTwoColumns('Resampling Method', algaeForm::selectWithArray('resampling_method', $this->resampling_method, $resampling_methods), False);

    echo '<tr><td colspan=2></td></tr>';
    echo '<tr><td colspan=2></td></tr>';
    echo '<tr><td colspan=2>Parameters for initial signature modeling:</td></tr>';
    
    $cutoff_units = array('Absolute', 'MinMax', 'Percentile');
    algaeTable::writeTwoColumns('Cutoff Units', algaeForm::selectWithArray('cutoff_units', $this->cutoff_units, $cutoff_units), False);
    
    algaeTable::writeTwoColumns('Lower Cutoff', algaeForm::inputText('lower_cutoff', $this->lower_cutoff, 10), False);
    algaeTable::writeTwoColumns('Upper Cutoff', algaeForm::inputText('upper_cutoff', $this->upper_cutoff, 10), False);
  
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
    $this->input_raster->rowid = algaeForm::cleanInput($_POST['raster_file_rowid_fk']);
    $this->legend_file->rowid = algaeForm::cleanInput($_POST['legend_file_rowid_fk']);
    $this->output_prefix = algaeForm::cleanInput($_POST['output_prefix']);
    $this->resampling_method = algaeForm::cleanInput($_POST['resampling_method']);
    $this->cutoff_units = algaeForm::cleanInput($_POST['cutoff_units']);
    $this->lower_cutoff = algaeForm::cleanInput($_POST['lower_cutoff']);
    $this->upper_cutoff = algaeForm::cleanInput($_POST['upper_cutoff']);
    $this->legend_from_database = algaeForm::cleanInput($_POST['legend_from_database']);
    // $this->output_type = algaeForm::cleanInput($_POST['output_type']);
    //
    // ----- write parameters to JSON so they can be saved generically with the geoprocess
    //
    $a = array();
    $a['raster_file_rowid_fk'] = $this->input_raster->rowid;
    $a['legend_file_rowid_fk'] = $this->legend_file->rowid;
    $a['output_prefix'] = $this->output_prefix;
    $a['resampling_method'] = $this->resampling_method;
    $a['cutoff_units'] = $this->cutoff_units;
    $a['lower_cutoff'] = $this->lower_cutoff;
    $a['upper_cutoff'] = $this->upper_cutoff;
    $a['legend_from_database'] = $this->legend_from_database;
    // $a['output_type'] = $this->output_type;
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
  /**
   * Check if record is ok to write.
   * {@inheritDoc}
   * @see slateGeoprocess::okToWrite()
   */
  public function okToWrite($showErrors = True)
  // --------------------------------------------------------------------------
  {
    $num_errors = 0;
    if (! parent::okToWrite($showErrors)) $num_errors += 1;
    algaeForm::checkRequiredValue($this->input_raster->rowid, 'Raster Rowid', $num_errors, $showErrors);
    algaeForm::checkRequiredValue($this->output_prefix, 'Output Prefix', $num_errors, $showErrors);
    algaeForm::checkRequiredValue($this->resampling_method, 'Resampling Method', $num_errors, $showErrors);
    if ($num_errors == 0) return True;
    return False;
  }
  
}





