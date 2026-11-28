<?php

/**

  slate | Create a generic GeoProcess.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
*/

class slateGeoProcGeneric extends slateGeoProcess
{
  
  public $parameters_from_dex;
  
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
    $this->mainTabName = 'Parameters';
    $this->parameters_from_dex = null;
    $this->initFromDEX();
  }
  
  protected function initFromDEX()
  // --------------------------------------------------------------------------
  {
    global $app;
    $urlVar = 'process';
    echo 'DEBUG: In initFromDEX().<p />';
    if (isset($_REQUEST[$urlVar]))
    {
      $process_name = algaeForm::cleanInput($_REQUEST[$urlVar]);
      foreach ($app->config->geoprocesses_json as $item)
      {
        if ($item->name == $process_name)
        {
          $this->parameters_from_dex = $item->parameters;
          $this->mainTabName = $item->name;
          $this->command = $item->command;
          break;
        }
      }
      if ($this->parameters_from_dex != null)
      {
        //
        // ----- dynamically add variables to the class for each parameter
        //
        foreach ($this->parameters_from_dex as $parameter)
        {
          $this->{$parameter->controlId} = null;
        }
      }
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
    parent::addDerivedFieldsToForm();
    if ( ($this->parameters_from_dex != null) && (count($this->parameters_from_dex) > 0) )
    {
      echo '<p />'; 
      foreach ($this->parameters_from_dex as $parameter)
      {
        if ($parameter->type == 'textInput')
        {
          algaeTable::writeTwoColumns($parameter->prompt, 
            algaeForm::inputText($parameter->controlId, $this->{$parameter->controlId}, 40), False);
        }
      }
    }
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
    // ----- write parameters to JSON so they can be saved generically with the geoprocess
    //
    $a = array();
    foreach ($this->parameters_from_dex as $parameter)
    {
      $a[$parameter->controlId] = $this->{$parameter->controlId};
    }
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
}





