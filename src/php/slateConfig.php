<?php

/**
*/

class slateConfig extends algaeConfig
{
  
  public $geoprocesses_folder;
  public $places_folder;
  public $model_palette_file;
  public $projects_base_folder;
  
  /**
   * Constructor.
   */
  public function __construct($load_detailed_config = True, $debug = False)
  // --------------------------------------------------------------------------
  {
    //
    // ----- important to load framework config first
    //
    parent::__construct($load_detailed_config, $debug);
    //
    // ----- setup main app names and config
    //
    $this->app_name = 'slate';
    $this->app_database = 'slate';
    $this->app_folder = 'slate';
    $this->config_path = $this->getAppConfigParameter('slate', 'configPath');
    //
    // -----
    //
    $this->projects_base_folder = '/opt/slate';
    $this->geoprocesses_folder = 'gp';
    $this->places_folder = 'pl';
    $this->model_palette_file = '/var/www/html/slate/palettes/model_colors.txt';
    $this->similarity_prefix = 'sim_';
    $this->rowid_directory_levels = 2;
    $this->thumbnail_suffix = '_thumb.png';
    $this->colored_suffix = '_colored.png';
    $this->annotated_suffix = '_annotated.png';
    $this->overlay_suffix = '_overlay.png';
    $this->run_geoprocesses_app = 'rungeoprocesses.py';
    $this->source_data_directory = 'source_data';
    $this->vector_data_sub_directory = 'vector';
    $this->raster_data_sub_directory = 'raster';
    $this->other_data_sub_directory = 'other';
    //
    // ----- load detailed configuration files
    //
    if ($load_detailed_config)
    {
      $this->loadConfigFiles();
    }
  }
  
}




