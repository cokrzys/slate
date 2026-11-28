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
  
  public $parameters_from_config;
  public $unique_id;
  
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
    $this->parameters_from_config = null;
    $this->unique_id = null;
    $this->initFromURLProcessName();
  }
  
  protected function initFromParameters()
  // --------------------------------------------------------------------------
  {
    global $app;
    $a = json_decode($this->parameters, true);
    if ($a != null)
    {
      if (array_key_exists('unique_id', $a))
      {
        $this->unique_id = $a['unique_id'];
        foreach ($app->config->geoprocesses_json as $item)
        {
          if ($item->uniqueId == $this->unique_id)
          {
            $this->initFromConfig($item);
            $this->decodeParameters();
            break;
          }
        }
      }
    }
  }
  
  protected function initFromURLProcessName()
  // --------------------------------------------------------------------------
  {
    global $app;
    $urlVar = 'process';
    if (isset($_REQUEST[$urlVar]))
    {
      $process_name = algaeForm::cleanInput($_REQUEST[$urlVar]);
      foreach ($app->config->geoprocesses_json as $item)
      {
        if ($item->name == $process_name)
        {
          $this->initFromConfig($item);
          break;
        }
      }
    }
  }
  
  protected function initFromConfig($config)
  // --------------------------------------------------------------------------
  {
    $this->parameters_from_config = $config->parameters;
    $this->mainTabName = $config->name;
    $this->command = $config->command;
    $this->unique_id = $config->uniqueId;
    if ($this->parameters_from_config != null)
    {
      //
      // ----- dynamically add variables to the class for each parameter
      //
      foreach ($this->parameters_from_config as $parameter)
      {
        $this->{$parameter->controlId} = null;
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
    if ( ($this->parameters_from_config != null) && (count($this->parameters_from_config) > 0) )
    {
      echo '<p />'; 
      foreach ($this->parameters_from_config as $parameter)
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
      foreach ($this->parameters_from_config as $parameter)
      {        
        if (array_key_exists($parameter->controlId, $a))
        {
          $this->{$parameter->controlId} = $a[$parameter->controlId];
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
    // the single quotes do not need to be escaped with trim(pg_escape_string($str))
    // in the algaeForm::cleanInput() call below, seems like this is different from the paramaterized
    // newer logic vs writing in-line sql like before
    //
    if (property_exists($this, 'sql'))
    {
      $this->sql = $_POST['sql'];
    }
    //
    // ----- write parameters to JSON so they can be saved generically with the geoprocess
    //
    $a = array();
    $a['unique_id'] = $this->unique_id;
    foreach ($this->parameters_from_config as $parameter)
    {
      $a[$parameter->controlId] = $this->{$parameter->controlId};
    }
    $this->parameters = json_encode($a, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
  }
  
}





