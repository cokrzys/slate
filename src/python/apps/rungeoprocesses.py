#!/usr/bin/python3

"""

 slate | Run one or more geoprocesses.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import json
import builtins
import math
import argparse
from osgeo import gdal

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB

sys.path.append('../modules')

from slateapp import slateApp
# from slaterungeoprocesses import slateRunGeoprocesses

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Run one or more geoprocesses.')
parser.add_argument("-rowid", "--geoprocess_rowid_fk", type=int, help="Rowid of a single geoprocess to run.", default=None)
parser.add_argument("--project_rowid_fk", type=int, help="Rowid of project from sp.project.", default=None)
parser.add_argument("--where", help="SQL WHERE clause to select multiple geoprocesses to run.", default=None)
parser.add_argument("--resolution_rowid_fk", required=True, help="Rowid of resolution from ref.resolution.")
parser.add_argument("--process_rowid_fk", type=int, help="Rowid from core.process that started the run.", default=None)
parser.add_argument("--max_to_run", type=int, help="Maximum number of geoprocesses to run.", default=None)
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
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
    #
    # ----- run
    #
    # w = slateRunGeoprocesses(db, args)
    # w.run()
    #
    # ----- close database
    #
    db.close()




