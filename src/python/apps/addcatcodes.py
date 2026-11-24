#!/usr/bin/python3

"""

 slate | Add category codes to a shapefile.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import argparse
import math
import shapefile
from shutil import copyfile
import xml.etree.ElementTree as ET
import code
import json
import builtins

from algaeapp import algaeApp
from algaedb import algaeDB

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

# import qgisstyle as qgisstyle
from slateapp import slateApp
from slatecategorycoder import slateCategoryCoder

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Add categoory codes to a shapefile.')
parser.add_argument("input_file", help="Input shapefile.")
parser.add_argument("output_file", help="Output shapefile.")
parser.add_argument("--legend_rowid_fk", default=None, help="Optional rowid for a legend in the database.")
parser.add_argument("-af", "--attribute_field", required=True, default=None, help="Attribute field.")
parser.add_argument("-ot", "--output_type", default="Byte", help="Output raster type, used to check the number of unique values.")
parser.add_argument("-debug", help="Debug messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

app = slateApp()
builtins.app = app # add app to builtins for true globl access

#
# ----- open database
#
db = algaeDB()
if db.open(app.config.app_database, app.config.database_port, app.config.database_username,
           app.config.database_password):
    print('Database ' + app.config.app_database + ' opened.')

    cc = slateCategoryCoder(db, args)
    cc.run()
    db.close()













