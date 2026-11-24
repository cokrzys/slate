"""

 Slate | Setup or check codes for categorical data.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import math
import shapefile
import subprocess
import datetime
import inspect

import numpy as np

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB

from slateapp import slateApp
from slatelegend import slateLegend

class slateCategoryCoder:
    
    def __init__(self, db, args):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.db = db
        self.args = args
        self.debug = self.args.debug
                            
    def run(self):
    #------------------------------------------------------------------------------
        """
        """
        #
        # ----- read input shapefile
        #
        r = shapefile.Reader(self.args.input_file)
        if r == None:
            sys.exit("ERROR: Unable to open the file %r" + self.args.input_file)
        
        #
        # ----- read shapes
        #
        polygons = r.shapes()
        
        #
        # ----- no shapes read
        #
        if len(polygons) <= 0: sys.exit("ERROR: No objects in the shapefile.")
        
        #
        # ----- show number of shapes read
        #
        print("Read %r shapes from %s." % (len(polygons), self.args.input_file))
        
        if self.debug: print(r.fields)
        
        #
        # ----- find the attribute field definition
        #
        code_field = None    
        for field in r.fields:
            if field[0] == self.args.attribute_field:
                code_field = field
        
        #
        # ----- attribute field not found
        #
        if code_field == None: sys.exit("ERROR: Attribute field %r not found in the shapefile.  Field names are case sensitive." % args.attribute_field)
        
        if self.debug: print(code_field)
        
        #
        # ----- setup output shapefile
        #
        w = shapefile.Writer(self.args.output_file, shapeType=r.shapeType)
        w.field(code_field[0], code_field[1], code_field[2], code_field[3])
        w.field('slate_code', 'N')
        
        #
        # ----- get unique sorted list of attribute values
        #
        unique = []
        for shaperec in r.iterShapeRecords():
            strval = str(shaperec.record[self.args.attribute_field])
            if strval not in unique:
                unique.append(strval)
        unique.sort()
                
        print("%r unique values found for field %r." % (len(unique), self.args.attribute_field))
        if not slateApp.in_gdal_unit_range(self.args.output_type, len(unique)):
            print("ERROR: Number of unique values is greater than the " + self.args.output_type + " output data range.")
        
        if self.args.legend_rowid_fk == None:
            sl = slateLegend()
            sl.debug = self.debug
            filename =  os.path.basename(self.args.input_file)
            filename_parts = os.path.splitext(filename)
            legend_name = filename_parts[0] + ' | ' + self.args.attribute_field
            print(legend_name)
            sl.make_legend(self.db, legend_name, unique)
        
        #
        # ----- loop and create output shapefile
        #
        for shaperec in r.iterShapeRecords():
            w.record(shaperec.record[self.args.attribute_field], unique.index(str(shaperec.record[self.args.attribute_field])) + 1)
            w.shape(shaperec.shape)
            
            
        #
        # ----- create the legend file
        #
        # create_legend(self.args.output_legend_file, unique, style_list)
        
        #
        # ----- close applicable files
        #
        w.close()
        
        #
        # ----- copy the projection file if it exists
        #
#         input_prj_filename = os.path.splitext(self.args.input_file)[0] + '.prj'
#         
#         if os.path.isfile(input_prj_filename):
#             output_prj_filename = os.path.splitext(self.args.output_file)[0] + '.prj'
#             copyfile(input_prj_filename, output_prj_filename)

        
        
        
        