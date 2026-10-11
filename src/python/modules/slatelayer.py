"""

 Slate | Layer and sp.layer support.
 
 A layer is a reference to a raster and associated metadata.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os

import numpy as np
from osgeo import gdal, osr

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase

from slateapp import slateApp
from refresolution import refResolution
from slategeoprocess import slateGeoProcess

class slateLayer(algaeTblBase):

    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.layer'
        self.filename = None
        self.num_cols = None
        self.num_rows = None
        self.num_nodata_cells = None
        self.data_format = None
        self.data_min = None
        self.data_max = None
        self.data_mean = None
        self.data_stddev = None
        self.data_q1 = None
        self.data_median = None
        self.data_q3 = None
        self.sig_lower_cutoff = None
        self.sig_upper_cutoff = None
        self.calc_begin_utc = None
        self.calc_end_utc = None
        self.geoprocess = slateGeoProcess()
        self.resolution = refResolution()
        #
        # ----- raster properties and data
        #
        self.xsize = None
        self.ysize = None
        self.geotransform = None
        self.projection = None
        self.epsg = None
        self.nodata_value = None
        self.data = None
    
    def read_row_from_database_with_geoprocess_and_resolution(self, db, geoprocess_rowid_fk, resolution_rowid_fk, deep_read = False):
    #------------------------------------------------------------------------------
        """
        Read a row from the database with a geoprocess rowid and resolution name.
        """
        sql = self.get_sql()
        sql += u""" WHERE {table}.geoprocess_rowid_fk = %(geoprocess_rowid_fk)s AND 
                    {table}.resolution_rowid_fk = %(resolution_rowid_fk)s""".format(table=self.table_name)
        parms = {'geoprocess_rowid_fk': geoprocess_rowid_fk, 'resolution_rowid_fk': resolution_rowid_fk}
        return self.read_row_from_database_with_sql(db, sql, parms, deep_read)
    
    def update_timestamp(self, db, timestamp_column, rowid = None, epoch = None):
    #------------------------------------------------------------------------------
        """
        """
        sql = u"""UPDATE {table} SET {column} = """.format(table=self.table_name, column=timestamp_column)
        if epoch == None:
            sql += 'NOW()'
        else:
            sql += "TO_TIMESTAMP(%(epoch)s)"
        sql += " WHERE rowid = %(rowid)s"
        if rowid == None: rowid = self.rowid
        if not db.execute_query(sql, {'rowid':rowid, 'epoch':epoch}):
            algaeApp.error_message('Unable to update layer ' + timestamp_column + ' for rowid ' + str(rowid) + '.')
            return False
        return True
    
    def update_calc_begin(self, db, rowid = None, epoch = None):
    #------------------------------------------------------------------------------
        """
        """
        return self.update_timestamp(db, 'calc_begin_utc', rowid, epoch)
    
    def update_calc_end(self, db, rowid = None, epoch = None):
    #------------------------------------------------------------------------------
        """
        """
        return self.update_timestamp(db, 'calc_end_utc', rowid, epoch)
    
    def update_last_updated(self, db, rowid = None, epoch = None):
    #------------------------------------------------------------------------------
        """
        Becoming obsolete, use calc_end_utc instead.
        This was directed at a timestamp of when the main data file was last updated.
        """
        return self.update_timestamp(db, 'last_updated_utc', rowid, epoch)
    
    def make_filename(self, prefix, resolution_folder, extension = '.tiff'):
    #------------------------------------------------------------------------------
        """
        """
        self.filename = prefix + '_' + resolution_folder + extension
    
    def get_fully_pathed_filename(self):
    #------------------------------------------------------------------------------
        """
        """
        if self.resolution.folder != None:
            return self.geoprocess.get_directory(self.resolution.folder) + self.filename
        algaeApp.error_message('Resolution folder does not exist in slateLayer.get_fully_pathed_filename().')
        return None
    
    def file_exists(self):
    #------------------------------------------------------------------------------
        """
        """
        filename = self.get_fully_pathed_filename();
        if filename != None and os.path.isfile(filename):
            return True
        return False
    
    def get_fully_pathed_filename_with_suffix(self, suffix, extension = '.tiff'):
    #------------------------------------------------------------------------------
        """
        """
        parts = os.path.splitext(self.filename)
        if len(parts) == 2:
            return self.geoprocess.get_directory(self.resolution.folder) + parts[0] + suffix + extension
        return None
    
    def get_fully_pathed_overlay_filename(self):
    #------------------------------------------------------------------------------
        """
        """
        return self.get_fully_pathed_filename_with_suffix(app.config.overlay_suffix, '.png')
    
    def ok_to_use(self, study_area, mask_layer, layer_to_check):
    #------------------------------------------------------------------------------
        """
        Check if the raster is ok to use.
        """
        num_errors = 0
        #
        # ----- projection check
        #
        if layer_to_check.epsg != study_area.srid_fk:
            algaeApp.error_message('Layer %r EPSG code %r does not match study area EPSG code %r.' % 
                (layer_to_check.filename, layer_to_check.epsg, study_area.srid_fk))
            num_errors += 1
        # 
        # ----- TODO: Check extents.
        #
        #
        # ----- check layer_to_check against mask_layer
        #
        if mask_layer.rowid != layer_to_check.rowid:
            #
            # ----- sizes
            #
            if layer_to_check.xsize != mask_layer.xsize:
                algaeApp.error_message('Layer %r numcols %r does not match mask layer numcols %r.' % 
                (layer_to_check.filename, layer_to_check.xsize, mask_layer.xsize))
                num_errors += 1
            if layer_to_check.ysize != mask_layer.ysize:
                algaeApp.error_message('Layer %r numrows %r does not match mask layer numrows %r.' % 
                (layer_to_check.filename, layer_to_check.ysize, mask_layer.ysize))
                num_errors += 1
            #
            # TODO: Check number of NoData cells.
            #
        if num_errors > 0: return False
        return True
    
    def read_data(self, metadata_only = False):
    #------------------------------------------------------------------------------
        """
        Read the data.
        """
        filename = self.get_fully_pathed_filename()
        if os.path.isfile(filename):
            input = gdal.Open(filename)
            if input != None:
                band = input.GetRasterBand(1)
                self.xsize = band.XSize
                self.ysize = band.YSize
                if band.GetNoDataValue() != None: self.nodata_value = band.GetNoDataValue()   
                # print('NoData value for %s is %r.' % (self.filename, self.nodata_value))             
                self.geotransform = input.GetGeoTransform()
                self.projection = input.GetProjection()
                if not metadata_only:
                    self.data = band.ReadAsArray(0, 0, band.XSize, band.YSize)
                #
                # ----- projection contains full details of the projection
                #       this is a workaround to get the EPSG code
                #       https://gis.stackexchange.com/questions/267321/extracting-epsg-from-a-raster-using-gdal-bindings-in-python
                #
                proj = osr.SpatialReference(wkt=input.GetProjection())
                self.epsg = int(proj.GetAttrValue('AUTHORITY',1))
                input = None
                return True
            else:
                algaeApp.error_message('Unable to open ' + filename + '.')
        return False
    
    def exists_obsolete(self, db):
    #------------------------------------------------------------------------------
        """
        Check if the layer is already in the database.
        Replace this with the method in tblbase using column metadata
        """
        sql = u"SELECT rowid FROM {table}".format(table=self.table_name)
        sql += u" WHERE geoprocess_rowid_fk = %(geoprocess_rowid_fk)s AND filename = %(filename)s"
        rowid = db.get_scalar_integer(sql, {'geoprocess_rowid_fk': self.geoprocess_rowid_fk, 'filename':self.filename})
        if rowid != None and rowid > 0: return True
        return False
    
    def get_layers_for_project(self, db, project_rowid_fk, resolution_rowid_fk):
    #------------------------------------------------------------------------------
        """
        """
        sql = u"""SELECT {table}.rowid
                FROM {table}
                INNER JOIN sp.geoprocess ON {table}.geoprocess_rowid_fk = {geoprocess_table}.rowid
                WHERE {geoprocess_table}.project_rowid_fk = %(project_rowid_fk)s 
                AND {table}.resolution_rowid_fk =  %(resolution_rowid_fk)s""".format(table=self.table_name, geoprocess_table=self.geoprocess.table_name)
        sql += " ORDER BY {table}.filename""".format(table=self.table_name)
        parms = {'project_rowid_fk': project_rowid_fk, 'resolution_rowid_fk': resolution_rowid_fk}
        data = db.get_all(sql, parms)
        if data != None:
            print('%r project layer(s) read.' % len(data))
            sa = []
            for row in data:
                sa.append(row[0])
            return sa
        return None
    
    def get_cell_coords(self, world_x, world_y):
    #------------------------------------------------------------------------------
        """
        Get cell coordinates for a pair of real world coordinates.
        """
        #
        # ----- origin and cell size information, for example:
        #       geotransform[0] = -180.0 upper-left x
        #       geotransform[1] = 1.0 x cell size
        #       geotransform[2] = 0.0
        #       geotransform[3] = 90.0 upper-left y
        #       geotransform[4] = 0.0
        #       geotransform[5] = -1.0 y cell size
        #       midpoint of upper-left cell = -179.5, 89.5
        #
        if self.geotransform is not None:
            px = int((world_x - self.geotransform[0]) / self.geotransform[1]) # x pixel
            py = int((world_y - self.geotransform[3]) / self.geotransform[5]) # y pixel
            return (px, py)
        return None
    
    def get_data_value(self, grid_x, grid_y):
    #------------------------------------------------------------------------------
        """
        Get a value from a grid.

        :param grid_x: X location in the grid from 0 to the x-dimension - 1.
        :type grid_x: integer

        :param grid_y: Y location in the grid from 0 to the y-dimension - 1.
        :type grid_y: integer
        """
        if self.data is not None:
            if len(self.data) > 0 and grid_y >= 0 and grid_y < len(self.data) and grid_x >= 0 and grid_x < len(self.data[0]):
                return self.data[grid_y, grid_x]
        return None
    
    def is_nodata(self, val):
    #------------------------------------------------------------------------------
        """
        """
        if self.nodata_value != None and val >= self.nodata_value - 0.1 and val <= self.nodata_value + 0.1:
            return True
        return False
    
    def create_data(self, xsize, ysize, type):
    #------------------------------------------------------------------------------
        """
        Create default data (raster).
        
        :param type: numpy data type to create the grid with.
        :type type: numpy type, i.e. np.byte, np.float32, see also:
        https://docs.scipy.org/doc/numpy/user/basics.types.html
        """
        self.xsize = xsize
        self.ysize = ysize
        self.data = np.zeros((ysize, xsize), type)
        
    def create_zero_raster_from_layer(self, layer, type):
    #------------------------------------------------------------------------------
        global app
        #
        # ----- basic error checking
        #
        if layer.xsize == None or layer.ysize == None:
            algaeApp.error_message('Missing xsize and/or ysize trying to create a layer from a layer.')
            return False
        if layer.geotransform == None or layer.projection == None:
            algaeApp.error_message('Missing the geotransform and/or projection trying to create a layer from a layer.')
            return False
        #
        # ----- create
        #
        self.create_data(layer.xsize, layer.ysize, type)
        self.geotransform = layer.geotransform
        self.projection = layer.projection
        return True
    
    def write_raster(self, filename, type, nodata_value):
    #------------------------------------------------------------------------------
        """
        Write the raster to a file.
        Example: write_raster('test.tiff', gdal.GDT_Byte)
        """
        driver = gdal.GetDriverByName("GTiff")
        output = driver.Create(filename, self.xsize, self.ysize, 1, type)
        if output != None:
            #
            # ----- set color table if defined
            #
            # if self.color_table != None: output.GetRasterBand(1).SetColorTable(self.color_table)
            #
            # ----- set output projection to the same as the input
            #
            output.SetGeoTransform(self.geotransform)
            output.SetProjection(self.projection)
            output.GetRasterBand(1).SetNoDataValue(nodata_value)
            output.GetRasterBand(1).WriteArray(self.data)
            output = None
            return True
        else:
            slateApp.error_message("Unable to create raster " + filename + '.');
        return False





    
    
    