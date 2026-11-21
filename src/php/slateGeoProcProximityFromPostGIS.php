<?php

/**

  slate | Proximity from a PostGIS query.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcProximityFromPostGIS extends slateGeoprocess
{
  
  public $max_distance;
  public $output_prefix;
  public $sql;
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
    $this->command = 'proximity_from_postgis.sh';
    $this->name = 'Proximity';
    $this->mainTabName = 'Proximity';
    $this->showBatchParametersTab = True;
    $this->data_type->name = 'Float32';
    $this->data_distribution->name = 'Proximity';
    $this->units->name = 'Meters';
    $this->max_distance = null;
    $this->output_prefix = null;
    $this->sql = null;
    $this->invert = False;
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
      if (array_key_exists('output_prefix', $a)) { $this->output_prefix = $a['output_prefix']; }
      if (array_key_exists('max_distance', $a)) { $this->max_distance = $a['max_distance']; }
      if (array_key_exists('invert', $a)) { $this->invert = algaeCore::getBoolean($a['invert']); }
      if (array_key_exists('sql', $a)) { $this->sql = $a['sql']; }
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
    $this->output_prefix = $this->project->getDefaultPrefix($this->output_prefix);
    algaeTable::writeTwoColumns('Output Prefix', algaeForm::inputText('output_prefix', $this->output_prefix, 40, algaeForm::REQUIRED) .
      $app->getDetailString($app->getFilenameTemplate()), False);
    algaeTable::writeTwoColumns('Max Distance', algaeForm::inputText('max_distance', $this->max_distance, 10), False);
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
    //
    // the single quotes do not need to be escaped with trim(pg_escape_string($str))
    // in the algaeForm::cleanInput() call below, seems like this is different from the paramaterized
    // newer logic vs writing in-line sql like before
    //
    $this->sql = $_POST['sql'];
    
    $a = array();
    $a['output_prefix'] = $this->output_prefix;
    $a['max_distance'] = $this->max_distance;
    $a['invert'] = algaeCore::getTrueFalse($this->invert);
    $a['sql'] = $this->sql;
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
  /**
   * Show sql tab.
   */
  protected function showSQLTab($id, $form)
  // --------------------------------------------------------------------------
  {
    echo '<div id="', $id, '">';
    echo '<textarea name="sql" id="sql" class="fullwidth" cols="110" rows="18">', algaeCore::toHtml($this->sql), '</textarea><p />';
    algaeCore::addJavaScript("algaefw.makeTextareaCodeMirror('sql', 'sql', 500);");
    echo '</div>';
  }
  
  public function showForm()
  // --------------------------------------------------------------------------
  {
    $f = new algaeForm();
    $mainTabId = 'main_tab';
    $existingRecordsId = 'existing_records_tab';
    $sqlTabId = 'sql_tab';
    
    if (isset($_REQUEST['rowid']))
    {
      $this->read_row_from_database_with_rowid($_REQUEST['rowid']);
      $this->decodeParameters();
      if ( (isset($_REQUEST['copy'])) && ($_REQUEST['copy'] == 'True') )
      {
        $this->rowid = null;
        $this->name .= ' (copy)';
      }
    }
    
    //
    // ----- setup tabs
    //
    $tabs_array = array(array('#' . $mainTabId, $this->mainTabName));
    $tabs_array[] = array('#' . $sqlTabId, 'SQL');
    $tabs_array[] = array('#' . $existingRecordsId, 'Existing GeoProcesses');
    
    $f->startForm(algaeForm::getDefaultToken($this));
    
    algaeForm::startTabs($tabs_array);
    
    //
    //
    //
    $this->showMainTabForm($mainTabId, $f);
    $this->showSQLTab($sqlTabId, $f);
    $this->showExistingRecordsTab($existingRecordsId);
    //
    // ----- end tabs
    //
    algaeForm::endTabs();
    //
    // ----- end form
    //
    echo '</form>';
    echo '<p />';
    echo '<p /><br />';
  }
  
}




