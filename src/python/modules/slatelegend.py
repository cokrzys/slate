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
from slateclass import slateClass

class slateLegend(algaeTblNamedObjectBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.legend'
        self.legend_items = None
    
    def update_descriptions_obsolete(self, db, layer_rowid_fk, legend_rowid_fk):
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
            
    def get_num_items(self):
    # ------------------------------------------------------------------------------
        """
        """
        if self.legend_items != None:
            return len(self.legend_items)
        return 0
            
    def load_legend_items(self, db):
    # ------------------------------------------------------------------------------
        """
        """
        from slatelegenditem import slateLegendItem
        self.legend_items = list()
        li = slateLegendItem()
        sql = li.get_sql(True)
        sql += ' WHERE ' + li.table_name + ".legend_rowid_fk = %(legend_rowid_fk)s ORDER BY int_code"
        parms = {'legend_rowid_fk': self.rowid}
        data = db.get_all(sql, parms)
        if data != None:
            for row in data:
                new_item = slateLegendItem()
                new_item.debug = self.debug
                new_item.read_row_from_database_with_rowid(db, row[0])
                if new_item.rowid != None:
                    self.legend_items.append(new_item)
            if self.debug: print(str(len(self.legend_items)) + ' legend item(s) read.')
        else:
            algaeApp.error_message('No legend items for legend ' + self.name + '.')
            
    def write_to_gdal_color_table(self, color_table):
    # ------------------------------------------------------------------------------
        """
        """
        if self.legend_items != None:
            for item in self.legend_items:
                rgb = algaeCore.html_to_rgb(item.html_color)
                if self.debug:
                    print('Index %r = rgb %r %r %r for html %r' % (item.int_code, rgb[0], rgb[1], rgb[2], item.html_color))
                color_table.SetColorEntry(item.int_code, (rgb[0], rgb[1], rgb[2]))
            
    def write_classes(self, db, layer_rowid_fk, bin_counts, nodata_value):
    # ------------------------------------------------------------------------------
        """
        """
        sc = slateClass()
        sc.delete_classes(db, layer_rowid_fk)
        #
        # ----- loop and write each class
        #
        for i in range(0, len(bin_counts[0])):
            sc.layer_rowid_fk = layer_rowid_fk
            sc.raster_value = int(bin_counts[0][i])
            sc.num_values = int(bin_counts[1][i])
            
            matched = False
            for item in self.legend_items:
                if item.int_code == sc.raster_value:
                    matched = True
                    if item.text_code != None:
                        sc.code = item.text_code
                    else:
                        sc.code = str(sc.raster_value)
                    sc.description = item.description
                    sc.html_color = item.html_color
                    sc.write(db)
                    break
            
            if not matched and sc.raster_value == nodata_value:
                sc.code = slateApp.NODATA_DESC
                sc.description = 'NoData'
                sc.html_color = slateApp.rgb_to_html(slateApp.NODATA_COLOR[0], slateApp.NODATA_COLOR[1], slateApp.NODATA_COLOR[2])
                sc.write(db)
            elif not matched:
                sc.code = str(sc.raster_value)
                sc.html_color = slateApp.get_random_html_color()
                sc.write(db)
                print('WARNING: Legend not found for raster value %r.' % sc.raster_value)
                
        

            
        







