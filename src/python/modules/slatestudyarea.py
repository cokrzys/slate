"""

  slate | Study area and support for the sp.study_area table.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
"""

import sys
import psycopg2
import json
import math

from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase

from slateconfig import slateConfig
from slateproject import slateProject
from slategeoprocess import slateGeoProcess
from slatelatlongbox import slateLatLongBox
from slatesourcefile import slateSourceFile
from slateplace import slatePlace
from refresolution import refResolution

class slateStudyArea(algaeTblBase):
    
    VIEW_LAT_LONG_BOUNDS = 'sp.view_study_area_lat_long_bounds'

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.study_area'
        self.srid_fk = None
        self.min_x = None
        self.max_x = None
        self.min_y = None
        self.max_y = None
        self.buffer = None
        self.project = slateProject()
        self.geoprocess = slateGeoProcess()
        self.lat_long_bounds = slateLatLongBox()
        self.lat_long_bounds_with_buffer = slateLatLongBox()
        self.source_file = slateSourceFile()
        self.resolution = refResolution()
        self.place = slatePlace();
    
    def read_row_from_database_with_project_rowid(self, db, project_rowid_fk, deep_read = False):
    #------------------------------------------------------------------------------
        """
        Read a row from the database with a project rowid.
        """
        sql = self.get_sql()
        sql += u""" WHERE {table}.project_rowid_fk = %(project_rowid_fk)s""".format(table=self.table_name)
        return self.read_row_from_database_with_sql(db, sql, {'project_rowid_fk': project_rowid_fk}, deep_read)
    
    def read_lat_long_bounds(self, db):
    #------------------------------------------------------------------------------
        """
        """
        sql = "SELECT * FROM {table} WHERE rowid = %(rowid)s".format(table=self.VIEW_LAT_LONG_BOUNDS)
        parms = {'rowid': self.rowid}
        data = db.get_all(sql, parms)
        if data != None:
            for row in data:
                self.lat_long_bounds.long_min = row[1]
                self.lat_long_bounds.lat_min = row[2]
                self.lat_long_bounds.long_max = row[3]
                self.lat_long_bounds.lat_max = row[4]
                self.lat_long_bounds_with_buffer.long_min = row[5]
                self.lat_long_bounds_with_buffer.lat_min = row[6]
                self.lat_long_bounds_with_buffer.long_max = row[7]
                self.lat_long_bounds_with_buffer.lat_max = row[8]
        return None
    
    def get_num_cols(self, resolution):
    #------------------------------------------------------------------------------
        """
        """
        return (self.max_x - self.min_x) / resolution
    
    def get_num_rows(self, resolution):
    #------------------------------------------------------------------------------
        """
        """
        return (self.max_y - self.min_y) / resolution
    
    def set_thumbnail_size(self, cell_size_x, cell_size_y):
    #------------------------------------------------------------------------------
        """
        """
        ncols = self.get_num_cols(cell_size_x)
        nrows = self.get_num_rows(cell_size_y)
        # echo 'DEBUG: Original aspect = ', $ncols / $nrows, '<p />';
        x2 = math.ceil((ncols / nrows) * app.config.thumbnail_best_height)
        if x2 <= app.config.thumbnail_best_width:
          # echo 'DEBUG: Height best aspect = ', $x2 / $app->thumbnailBestHeight, '<p />';
          # echo 'DEBUG: ', $x2, ' x ', $app->thumbnailBestHeight, '<p />';
          self.thumb_x = x2
          self.thumb_y = app.config.thumbnail_best_height
        else:
          y2 = math.ceil((nrows / ncols) * app.config.thumbnail_best_width)
          # echo 'DEBUG: Width best aspect = ', $app->thumbnailBestWidth / $y2, '<p />';
          # echo 'DEBUG: ', $app->thumbnailBestWidth, ' x ', $y2, '<p />';
          self.thumb_x = app.config.thumbnail_best_width
          self.thumb_y = y2
        







