<?php

/**
*/

class slateConfig extends algaeConfig
{
  
  public $geoprocesses_folder;
  public $placed_folder;
  public $model_palette_file;
  
  /**
   * Constructor.
   */
  public function __construct($verbose = False, $load_detailed_config = True)
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    $this->app_name = 'slate';
    $this->app_database = 'slate';
    $this->app_folder = 'slate';
    //
    // -----
    //
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
    //
    // ----- load detailed configuration files
    //
    if ($load_detailed_config)
    {
      $this->loadConfigFiles();
    }
  }
  
}




