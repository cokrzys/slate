#!/usr/bin/python3

"""

 slate | Run one or more geoprocesses.

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

sys.path.append(sys.path[0] + '/classes')

from slateapp import slateApp
from slaterungeoprocesses import slateRunGeoprocesses

import math
import argparse

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

if app.have_db_connection_parms():
    if args.verbose: app.settings.show()
    db = algaeDB()
    #
    # ----- open database
    #
    if db.open(app.settings.app_database, app.settings.database_port, app.settings.database_username,
               app.settings.database_password):
        print('Database opened.')
        #
        # ----- run
        #
        w = slateRunGeoprocesses(db, args)
        w.run()
        #
        # ----- close database
        #
        db.close()




