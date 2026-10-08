"""

 slate | App config base class.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys

from algaeconfig import algaeConfig

class slateConfig(algaeConfig):
  
    def __init__(self, load_detailed_config = True, debug = False):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        #
        # ----- config init in parent
        #
        super().__init__(load_detailed_config, debug)
        #
        # ----- next config init for app, changing name first is important
        #
        self.app_name = 'slate'
        self.app_database = 'slate'
        self.config_path = self.getAppConfigParameter(self.app_name, 'configPath')
        #
        # ----- could be changed via an external configuration file
        #
        self.geoprocesses_folder = 'gp'
        self.places_folder = 'pl'
        self.low_resolution_folder = 'r01'
        self.medium_resolution_folder = 'r02'
        self.high_resolution_folder = 'r03'
        self.palettes_folder = '/var/www/html/slate/palettes/'
        self.model_palette_file = '/var/www/html/slate/palettes/qgis_spectral_5_color.txt'
        self.similarity_prefix = 'sim_'
        self.rowid_directory_levels = 2
        self.scripts_folder = self.getAppConfigParameter(self.app_name, 'scriptsPath')
        self.python_apps_folder = self.getAppConfigParameter(self.app_name, 'pythonAppsPath')
        self.thumbnail_best_width = 700
        self.thumbnail_best_height = 200
        self.colored_suffix = '_colored'
        self.annotated_suffix = '_annotated'
        self.overlay_suffix = '_overlay'
        self.files_folder = 'file'
        self.nodata_byte = 255
        self.nodata_float32 = -99999.99
        #
        #
        #
        if load_detailed_config: self.loadConfigFiles()

        
    
