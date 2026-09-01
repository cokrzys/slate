<?php

/**
*/

class slateConfig extends algaeConfig
{
  
  #
  # ----- items that cannot be changed
  #
  const LOW_RESOLUTION_NAME = 'Low';
  const MEDIUM_RESOLUTION_NAME = 'Medium';
  const HIGH_RESOLUTION_NAME = 'High';
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->app_name = 'slate';
    #
    # ----- could be changed via an external configuration file
    #
    $this->geoprocesses_folder = 'gp';
    $this->places_folder = 'pl';
    $this->low_resolution_folder = 'r01';
    $this->medium_resolution_folder = 'r02';
    $this->high_resolution_folder = 'r03';
    $this->model_palette_file = '/var/www/html/sladah/palettes/model_colors.txt';
    $this->similarity_prefix = 'sim_';
    $this->rowid_directory_levels = 2;
    $this->thumbnail_suffix = '_thumb.png';
    $this->colored_suffix = '_colored.png';
    $this->annotated_suffix = '_annotated.png';
    $this->overlay_suffix = '_overlay.png';
    $this->run_geoprocesses_app = 'rungeoprocesses.py';
    #
    #
    #
    $this->loadAppConfig();
    $this->loadDataExchangeConfig();
    
    $object_vars = get_object_vars($this);
    
    foreach ($object_vars as $name => $value) 
    {
      # echo 'DEBUG: ', $name, ' = ', $value, '<p />';
      if ( (array_key_exists(strtoupper($name), $this->config)) && ($value != $this->config[strtoupper($name)]) )
      {
        $this->{$name} = $this->config[strtoupper($name)];
        # echo 'DEBUG: Config value ', $name, ' changed from ', $value, ' to ', $this->config[strtoupper($name)], '<p />';
      }
    }
  }
  
  public function get_resolution_folder_from_name($resolution_name)
  #------------------------------------------------------------------------------
  {
    if ($resolution_name == slateConfig::LOW_RESOLUTION_NAME)
    {
      return $this->low_resolution_folder;
    }
    elseif ($resolution_name == slateConfig::MEDIUM_RESOLUTION_NAME)
    {
      return $this->medium_resolution_folder;
    }
    elseif ($resolution_name == slateConfig::HIGH_RESOLUTION_NAME)
    {
      return $this->high_resolution_folder;
    }
    else
    {
      algaeApp::errorMessage('Unsupported resolution name ' . resolution_name . '.');
    }
    return null;
  }
  
}




