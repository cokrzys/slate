"""

 slate | Geoprocess and sp.geoprocess support.
 
 Supports the rungeoprocesses application.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import psycopg2
import json

from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase
from algaetblcoreappuser import algaeTblCoreAppUser

from refdatatype import refDataType
from refoutputtype import refOutputType
from refdatagroup import refDataGroup
from refunits import refUnits
from slateproject import slateProject
from slateshapefile import slateShapefile

class slateGeoProcess(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.geoprocess'
        self.name = None
        self.php_class = None
        self.command = None
        self.parameters = None
        self.batch_parameters = None
        self.num_decimals = 2
        self.description = None
        self.data_type = refDataType()
        self.output_type = refOutputType()
        self.data_group = refDataGroup()
        self.units = refUnits()
        self.project = slateProject()
        self.require_mask_layer = True  # useful to not process when changing resolutions and the mask hasn't been made yet
    
    def read_row_from_database_with_project_rowid_does_not_work(self, db, project_rowid_fk, deep_read = True):
    #------------------------------------------------------------------------------
        """
        Read a row from the database with a project rowid.
        """
        sql = self.get_sql()
        sql += u""" WHERE {table}.project_rowid_fk = %(project_rowid_fk)s""".format(table=self.table_name)
        return self.read_row_from_database_with_sql(db, sql, {'project_rowid_fk': project_rowid_fk}, deep_read)
    
    def get_directory(self, resolution_folder):
    # ------------------------------------------------------------------------------
        """
        """        
        return self.project.get_item_directory(resolution_folder, app.config.geoprocesses_folder, self.rowid)
    
    def create_directory(self, resolution_folder):
    # ------------------------------------------------------------------------------
        """
        """
        return self.project.create_item_directory(resolution_folder, app.config.geoprocesses_folder, self.rowid)
    
    def add_extra_data(self, db, parms):
    # ------------------------------------------------------------------------------
        """
        """
    
    def parameters_to_variables(self, db, parms):
    # ------------------------------------------------------------------------------
        """
        """
        if self.parameters != None:
            j = json.loads(self.parameters)
            for key, value in j.items():
                parms[key] = value
                #
                #
                #
                if key == 'shapefile_rowid_fk':
                    shp = slateShapefile()
                    shp.read_row_from_database_with_rowid(db, value, True)
                    if shp.rowid != None:
                        parms['shapefile'] = shp.source_filename
                        # setattr(self, 'shapefile', shp)
            #
            # ----- load extra data typically in a derived class
            #
            self.add_extra_data(db, parms)
                    
        
        







