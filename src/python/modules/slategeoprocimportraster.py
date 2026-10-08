"""

 slate | Geoprocess to import a raster.
 
 Specialized geoprocess with extra parameters used when importing a raster.
 Typically loads and sends the parameters to a shell script via a JSON file.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import psycopg2
import json

from slategeoprocess import slateGeoProcess
from slatesourcefile import slateSourceFile

class slateGeoProcImportRaster(slateGeoProcess):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()

    def get_columns(self):
    # ------------------------------------------------------------------------------
        """
        :return:
        """
        columns = super().get_columns()
        return columns
    
    def filename_to_parms(self, db, parms, rowid_name, parms_name):
    # ------------------------------------------------------------------------------
        """
        """
        if rowid_name in parms:
            rowid = parms[rowid_name]
            if rowid != None and len(str(rowid)) > 0:
                f = slateSourceFile()
                f.read_row_from_database_with_rowid(db, int(rowid), True)
                parms[parms_name] = f.filename
    
    def add_extra_data(self, db, parms):
    # ------------------------------------------------------------------------------
        """
        
        """
        self.filename_to_parms(db, parms, 'raster_file_rowid_fk', 'rasterFilename')
        self.filename_to_parms(db, parms, 'legend_file_rowid_fk', 'legendFilename')
        
        tag = 'legend_from_database'
        if tag in parms:
            parms['legendRowid'] = parms[tag]
        else:
            parms['legendRowid'] = 0
        
        
        







