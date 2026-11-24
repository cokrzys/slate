<?php

/**

  slate | Categorical from a shapefile GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcCategorical extends slateGeoprocess
{
  
  public $source_file;
  public $output_prefix;
  public $attribute_field;
  public $legend;
  
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
    $this->command = 'categorical_from_shapefile.sh';
    $this->name = 'Categorical from Shapefile';
    $this->mainTabName = $this->name;
    $this->data_distribution->name = 'Categorical';
    $this->num_decimals = 0;
    $this->data_type->name = 'Byte';
    $this->source_file = new slateSourceFile();
    $this->legend = new slateLegend();
    $this->output_prefix = null;
    $this->attribute_field = null;
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
      if (array_key_exists('legend_rowid_fk', $a))
      {
        $this->legend->rowid = $a['legend_rowid_fk'];
        $this->legend->read_row_from_database_with_rowid($this->legend->rowid);
      }
      if (array_key_exists('output_prefix', $a)) { $this->output_prefix = $a['output_prefix']; }
      if (array_key_exists('attribute_field', $a)) { $this->attribute_field = $a['attribute_field']; }
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
    algaeTable::writeTwoColumns('Shapefile', slateSourceFile::selectShapefile('source_file_rowid_fk',
      $this->source_file->filename, True), False);
    algaeTable::writeTwoColumns('Attribute Field', algaeForm::inputText('attribute_field', $this->attribute_field, 50, algaeForm::REQUIRED), False);
    $this->output_prefix = $this->project->getDefaultPrefix($this->output_prefix);
    algaeTable::writeTwoColumns('Output Prefix', algaeForm::inputText('output_prefix', $this->output_prefix, 40, algaeForm::REQUIRED) .
      $app->getDetailString($app->getFilenameTemplate()), False);
    algaeTable::writeTwoColumns('Legend', slateLegend::select('legend_rowid_fk', $this->legend->name, False), False);
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
    $this->legend->rowid = algaeForm::cleanInput($_POST['legend_rowid_fk']);
    //
    // ----- write parameters to JSON so they can be saved generically with the geoprocess
    //
    $a = array();
    $a['source_file_rowid_fk'] = $this->source_file->rowid;
    $a['legend_rowid_fk'] = $this->legend->rowid;
    $a['output_prefix'] = $this->output_prefix;
    $a['attribute_field'] = $this->attribute_field;
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
}






