"""

 Slate | Class and sp.class support.
 
 A layer is a reference to a raster and associated metadata.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys

from algaedb import algaeDB
from algaetblbase import algaeTblBase

from slatelayer import slateLayer

class slateClass(algaeTblBase):

    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.class'
        self.layer = slateLayer()
        self.raster_value = None
        self.num_values = None
        self.code = None
        self.html_color = None
        self.description = None
        
    def delete_classes(self, db, layer_rowid_fk):
    #------------------------------------------------------------------------------
        """
        """
        sc = slateClass()
        sql = u"DELETE FROM {table} WHERE layer_rowid_fk = %(layer_rowid_fk)s".format(table=sc.table_name)
        parameters = {}
        parameters['layer_rowid_fk'] = layer_rowid_fk
        return db.execute_query(sql, parameters)
    
    def get_num_classes(self, db, layer_rowid_fk):
    #------------------------------------------------------------------------------
        """
        """
        sc = slateClass()
        sql = u"SELECT COUNT(rowid) FROM {table} WHERE layer_rowid_fk = %(layer_rowid_fk)s".format(table=sc.table_name)
        parameters = {}
        parameters['layer_rowid_fk'] = layer_rowid_fk
        return db.get_scalar_integer(sql, parameters)
    
    
    
    