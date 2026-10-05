<?php

/**

  slate | Mask GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcMask extends slateGeoprocess
{
  
  public $source_file;
  public $output_prefix;
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
    $this->command = 'create_mask.sh';
    $this->name = 'Mask';
    $this->mainTabName = 'Mask';
    $this->output_prefix = null;
    $this->invert = False;
    //
    //
    //
    $this->data_group->name = 'Mask';
    $this->data_distribution->name = 'Categorical';
    $this->num_decimals = 0;
    $this->data_type->name = 'Byte';
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
        $this->source_file->rowid = $a['source_file_rowid_fk'];
        if (intval($this->source_file->rowid) > 0)
        {
          $this->source_file->read_row_from_database_with_rowid($this->source_file->rowid);
        }
      }
      $this->output_prefix = $a['output_prefix'];
      $this->invert = algaeCore::getBoolean($a['invert']);
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
    algaeTable::writeTwoColumns('Shapefile', slateSourceFile::selectShapefile('source_file_rowid_fk', 
      $this->source_file->filename, False), False);
    $this->output_prefix = $this->project->getDefaultPrefix($this->output_prefix);
    algaeTable::writeTwoColumns('Output Prefix', algaeForm::inputText('output_prefix', $this->output_prefix, 40, algaeForm::REQUIRED) .
      $app->getDetailString($app->getFilenameTemplate()), False);
    algaeTable::writeTwoColumns('Invert', algaeForm::checkbox('invert', 'invert', array(), $this->invert), False);
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
    $this->source_file->rowid = algaeForm::cleanInput($_POST['source_file_rowid_fk']);
    $this->output_prefix = algaeForm::cleanInput($_POST['output_prefix']);
    $this->invert = algaeForm::processCheckbox('invert', False, isset($_POST['submit']));
    //
    // ----- write parameters to JSON so they can be saved generically with the geoprocess
    //
    $a = array();
    $a['source_file_rowid_fk'] = $this->source_file->rowid;
    $a['output_prefix'] = $this->output_prefix;
    $a['invert'] = algaeCore::getTrueFalse($this->invert);
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
}





