"""

  slate | Overall project container and support for the sp.project table.
 
  Contains project name, data directory, abbreviation, and other overall project setup items.
  A study area is connected to a project and contains the projection, spatial boundary, and raster setup details.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
"""

import sys
import os
import time

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblcoreappuser import algaeTblCoreAppUser
from algaetblnamedobjectbase import algaeTblNamedObjectBase

from slateapp import slateApp

class slateProject(algaeTblNamedObjectBase):
    
    PROJECT_DIRECTORY = 0

    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.project'
        self.abbreviation = None
        self.folder = None
        self.public = None
        self.copyright = None
        self.app_user = algaeTblCoreAppUser()
        
    def get_directory(self, dirtype = None):
    #------------------------------------------------------------------------------
        """
        """
        if dirtype == None: dirtype = slateProject.PROJECT_DIRECTORY
        project_directory = os.path.join(app.config.projects_base_folder, self.folder)
        if self.debug: print('DEBUG: Project directory = ' + project_directory)
        if dirtype == slateProject.PROJECT_DIRECTORY:
          return project_directory
        else:
            slateApp.error_message('Unsupported project directory type ' + str(dirtype) + '.')
        return None
    
    def get_item_directory(self, resolution_folder, item_folder, rowid):
    # ------------------------------------------------------------------------------
        """
        """        
        if self.debug: print('DEBUG: in get_item_directory ' + self.folder)
        if resolution_folder != None and item_folder != None:
            d = os.path.join(self.get_directory(), resolution_folder, item_folder)
            newdir = algaeCore.get_path_from_rowid(rowid, app.config.rowid_directory_levels)
            if len(newdir) > 0:
                d += '/' + newdir + '/'
            return d
        else:
            if resolution_folder == None:
                algaeApp.error_message('Missing resolution folder in slateProject.get_item_directory().')
            elif item_folder == None:
                algaeApp.error_message('Missing item folder in slateProject.get_item_directory().')
        return None
    
    def create_item_directory(self, resolution_folder, item_folder, rowid):
    # ------------------------------------------------------------------------------
        """
        """
        if self.debug: print('DEBUG: in create_item_directory ' + resolution_folder + ', ' + item_folder + ', ' + str(rowid))
        d = self.get_item_directory(resolution_folder, item_folder, rowid)
        if self.debug: print('DEBUG: Checking for directory ' + d)
        if not os.path.exists(d):
            os.makedirs(d, exist_ok=True)
            time.sleep(0.25) # short pause in seconds to make sure new directory is recognized by os.path.exists()
        if os.path.exists(d):
            print('Item directory = ' + d)
            return True
        else:
            algaeApp.error_message('Unable to create directory ' + d)
            return False
        return False
    
    
    
    