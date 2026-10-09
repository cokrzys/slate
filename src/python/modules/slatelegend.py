"""

 slate | Support for legends and sp.legend table.

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

class slateLegend(algaeTblNamedObjectBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.legend'
    
    def update_descriptions(self, db, layer_rowid_fk, legend_rowid_fk):
    # ------------------------------------------------------------------------------
        """
        """        
        sql = """UPDATE sp.class SET description = sp.legend_item.name
                    FROM sp.legend_item
                    WHERE sp.class.layer_rowid_fk = %(layer_rowid_fk)s
                      AND sp.legend_item.legend_rowid_fk = %(legend_rowid_fk)s
                      AND sp.class.code = sp.legend_item.code"""
        if self.debug: print('DEBUG: Updating class descriptions, layer_rowid_fk = ' + str(layer_rowid_fk) + ', legend_rowid_fk = ' + str(legend_rowid_fk))
        if not db.execute_query(sql, {'layer_rowid_fk':layer_rowid_fk, 'legend_rowid_fk':legend_rowid_fk}):
            algaeApp.error_message('Problem updating the legend descriptions.')
            print('  layer_rowid_fk = ' + str(layer_rowid_fk) + ', legend_rowid_fk = ' + str(legend_rowid_fk))
    
        
            
        







