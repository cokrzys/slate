"""

  slate | Place and support for sp.place.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
"""

import sys
import os
import psycopg2
import json

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase
from algaetblnamedobjectbase import algaeTblNamedObjectBase

from slateconfig import slateConfig
from slateapp import slateApp
from slateproject import slateProject

class slatePlace(algaeTblNamedObjectBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.place'
        self.abbreviation = None
        self.latitude = None
        self.longitude = None
        self.project = slateProject()
    
    def get_directory(self, resolution_name):
    # ------------------------------------------------------------------------------
        """
        """        
        return self.project.get_item_directory(resolution_name, app.config.places_folder, self.rowid)
    
    def create_directory(self, resolution_name):
    # ------------------------------------------------------------------------------
        """
        """
        return self.project.create_item_directory(resolution_name, app.config.places_folder, self.rowid)
        
            
        







