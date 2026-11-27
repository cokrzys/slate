#!/usr/bin/python3

"""

 slate | Build strike lines from point data.
 
 Reads a space delimited codes field with data like "NB TA BE ABR_G MIC FLD" and parses it 
 into a unique set of individual items.  Originally written to support using
 the space delimited commodity lists in MRDS data.
 
 https://mrdata.usgs.gov/mrds/commodity-codes.html

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate
 
"""

from osgeo import gdal
import sys
import json
import math
import argparse
import shapefile
import builtins

from algaecore import algaeCore
from algaeapp import algaeApp

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Build strike lines from point data.')
parser.add_argument("input_shapefile", help="Input points shapefile.")
parser.add_argument("output_shapefile", help="Output lines shapefile.")
parser.add_argument("--orientation_field", default='strike', required=True, help="Field that contains strike orientation.")
parser.add_argument("-debug", help="Debug messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

app = slateApp()
builtins.app = app # add app to builtins for true globl access
gdal.DontUseExceptions() # prevents warning re: future exceptions use by default

#
# ----- read input shapefile
#
r = shapefile.Reader(args.input_shapefile)
if r == None:
    sys.exit("ERROR: Unable to open the file %r" + args.input_shapefile)

#
# ----- read shapes
#
shapes = r.shapes()

#
# ----- no shapes read
#
if len(shapes) <= 0: sys.exit("ERROR: No objects in the shapefile.")

#
# ----- show number of shapes read
#
print("Read %r shapes from %s." % (len(shapes), args.input_shapefile))

if args.debug: print(r.fields)

#
# ----- find the attribute field definition
#
strike_field = None    
for field in r.fields:
    if field[0] == args.orientation_field:
        strike_field = field

#
# ----- attribute field not found
#
if strike_field == None: sys.exit("ERROR: Strike field %r not found in the shapefile.  Field names are case sensitive." % args.orientation_field)

if args.debug: print(strike_field)


