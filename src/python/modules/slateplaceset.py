"""

  slate | A set of places and support for sp.place_set.

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
from algaetblsetbase import algaeTblSetBase

from slateconfig import slateConfig
from slateapp import slateApp
from slateproject import slateProject

class slatePlaceSet(algaeTblSetBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.place_set'
        self.project = slateProject()
        self.sql = None
        
    def read_places(self, db):
    # ------------------------------------------------------------------------------
        if self.sql != None:
            data = db.get_all(self.sql, ())
            if len(data) > 0:
                for row in data:
                    print('TODO')
        return None
        
            
        







