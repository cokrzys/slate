"""

 slate | App base class.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import random
from dotenv import dotenv_values
import numpy as np
from osgeo import gdal, osr

from algaecore import algaeCore
from algaeapp import algaeApp

from slateconfig import slateConfig

class slateApp(algaeApp):
    
    NODATA_BYTE = 255
    NODATA_FLOAT32 = -99999.99
    NODATA_COLOR = (0, 0, 0, 0)  # black
    NODATA_DESC = 'NoData'
    
    #
    # max number of items by type, for example geoprocesses and places
    # increment/decrement this by 999
    #
    MAX_ITEMS_FOR_EACH_TYPE = 999999
        
    NP2GDAL_CONVERSION = {
        "uint8": 1,
        "int8": 1,
        "uint16": 2,
        "int16": 3,
        "uint32": 4,
        "int32": 5,
        "float32": 6,
        "float64": 7,
        "complex64": 10,
        "complex128": 11,
    }
    
    #
    # ----- GDAL maximum unsigned integer values
    #
    GDAL_MAX_UINT_VALUE = {
        "Byte": 255,
        "UInt16": 65535,
        "UInt32": 4294967295,
    }
    
    def __init__(self, load_detailed_config = True, debug = False):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__(False, debug)
        self.config = slateConfig(load_detailed_config, debug) 
    
    @staticmethod
    def rgb_to_html(r, g, b):
    #------------------------------------------------------------------------------
        """
        Convert RGB values to a hex html color code.
        """
        def clamp(x): return max(0, min(x, 255))
        return "#{0:02x}{1:02x}{2:02x}".format(clamp(r), clamp(g), clamp(b))
    
    @staticmethod
    def get_random_html_color():
    #------------------------------------------------------------------------------
        """
        Get a random color.
        """
        return slateApp.rgb_to_html(random.randrange(256), random.randrange(256), random.randrange(256))
    
    @staticmethod
    def get_random_rgb_color():
    #------------------------------------------------------------------------------
        """
        Get a random color.
        """
        return (random.randrange(256), random.randrange(256), random.randrange(256))
    
    
    @staticmethod
    def in_gdal_unit_range(data_type, val):
    #------------------------------------------------------------------------------
        """
        Check if a value is within a specified GDAL unsigned integer data range.
        
        :param data_type: GDAL data type, 'Byte', 'UInt16' or 'UInt32'.
        :type data_type: string

        :param val: Value to check if it's in range.
        :type val: integer

        :returns: True if within range, false if not.
        :rtype: boolean
        """
        if data_type in slateApp.GDAL_MAX_UINT_VALUE:
            #
            # ----- run check, note that it's one below the max assuming the max is
            #       reserved for a nodata value
            #
            if val < slateApp.GDAL_MAX_UINT_VALUE[data_type]:
                return True
        else:
            print(data_type + ' not found in slateApp.GDAL_MAX_UINT_VALUE.')
        return False
        
    @staticmethod
    def get_bin_counts(grid, decimals = 0):
    #------------------------------------------------------------------------------
        """
        Get a list of unique values and counts for a grid.

        :param grid: 2D array of grid data.
        :type grid: array

        :param decimals: Number of decimals to use when transforming the data array to int value.
        :type decimals: integer

        :returns: None if no data is loaded or a tuple with values: (bin_number, count).
        :rtype: list
        """
        if len(grid) > 0:
            if (grid.dtype == 'float32' or grid.dtype == 'float64'):
                scalar = 1
                if decimals > 0: scalar = 10 ** decimals
                int_data = (grid * scalar).astype(int)
                bin_count = np.bincount(int_data.ravel())
            else:
                bin_count = np.bincount(grid.ravel())
            ii = np.nonzero(bin_count)[0]
            return (ii, bin_count[ii])
        return None
    
    @staticmethod
    def report_bin_counts(bin_count_tuple, decimals = 0):
    #------------------------------------------------------------------------------
        """
        Print a report showing bin counts.  Uses Python logging functionality
        via the default logger at logging.getLogger(ProspBase.LOG_NAME).
        
        :param bin_count_tuple: Tuple of unique values and cell counts, typically returned
            from a call to ProspBase.get_bin_counts().
        :type bin_count_tuple: tuple
        
        :param decimals: Number of decimals.
        :type decimals: integer
        """
        if bin_count_tuple is not None:
            total_count = 0.0
            percent = 0.0
            for i in range(0, len(bin_count_tuple[0])): total_count += float(bin_count_tuple[1][i])
            print('')
            print('Value   Count           Percent        ')
            print('------- --------------- ---------------')
            for i in range(0, len(bin_count_tuple[0])):
                if total_count > 0: percent = (float(bin_count_tuple[1][i]) / total_count) * 100.0
                print("{0:<7d} {1:<15,d} {2:<15.4f}".format(bin_count_tuple[0][i],
                                                                       bin_count_tuple[1][i],
                                                                       percent))
            print('')
            
    @staticmethod
    def transform_pt(db, x, y, from_epsg, to_epsg):
    #------------------------------------------------------------------------------
        """
        Transform a point from one coordinate system to another.  
        Uses a SQL call to PostGIS to do the transform.
        """
        sql = u"""SELECT ST_X(ST_Transform(pt, %(to_epsg)s)) AS trans_x, ST_Y(ST_Transform(pt, %(to_epsg)s)) AS trans_y
                    FROM
                    (
                      SELECT ST_PointFromText('POINT(%(x)s %(y)s)', %(from_epsg)s) AS pt
                    ) t"""
        parms = {'x': x, 'y': y, 'from_epsg': from_epsg, 'to_epsg': to_epsg}
        data = db.get_all(sql, parms)
        if data != None:
            if len(data) == 1:
                return data[0]
        return None
        
        
        
        
        
        
        
    
    
