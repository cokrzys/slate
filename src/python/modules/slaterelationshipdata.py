"""

  slate | Data associated with a relationship and support for sp.relationship_data.

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
from algaetbldatavaluebase import algaeTblDataValueBase

from slateconfig import slateConfig
from slateapp import slateApp
from slateproject import slateProject

class slateRelationshipData(algaeTblDataValueBase):

    def __init__(self, parentClass = 'slateRelationship', parentColumnName = 'relationship_rowid_fk', fieldClass = 'algaeTblField', fieldColumnName = 'field_rowid_fk'):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__(parentClass, parentColumnName, fieldClass, fieldColumnName)
        self.table_name = 'sp.relationship_data'
        
        
            
        







