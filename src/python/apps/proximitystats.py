#!/usr/bin/python3

"""

 slate | Proximity raster statistics.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

from osgeo import gdal
import sys
# import re  # to split on command but not within quoted strings
import json
import builtins

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp
from slatelayer import slateLayer
from slateproximitystats import slateProximityStats

import math
import argparse

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Proximity raster statistics.')
parser.add_argument("--layer_rowid_fk", required=True, help="Raster layer.")
parser.add_argument("--debug", help="Debug messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

app = slateApp()
builtins.app = app

#
# ----- open database
#
db = algaeDB()
if db.open(app.config.app_database, app.config.database_port, app.config.database_username,
           app.config.database_password):
    print('Database ' + app.config.app_database + ' opened.')
    #
    #
    #
    o = slateProximityStats()
    o.run(db, args)
    #
    # ----- close database
    #
    db.close()




