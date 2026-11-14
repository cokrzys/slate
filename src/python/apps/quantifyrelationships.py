#!/usr/bin/python3

"""

 slate | Quantify relationships between places and layers.

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
from slateplace import slatePlace
from slatestudyarea import slateStudyArea
from slatequantifyrelationships import slateQuantifyRelationships

import math
import argparse

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Quantify relationships between places and layers.')
parser.add_argument("--place_set_rowid_fk", type=int, required=True, help="Rowid of a place set.")
parser.add_argument("--resolution_rowid_fk", type=int, required=True, help="Rowid of resolution from ref.resolution.")
parser.add_argument("--process_rowid_fk", type=int, help="Rowid from core.process that started the run.", default=None)
parser.add_argument("--max_to_run", type=int, help="Maximum number of items to run.", default=None)
parser.add_argument("-debug", help="Debug messages.", action="store_true")
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
    # ----- run
    #
    qr = slateQuantifyRelationships(db, args)
    qr.run()
    #
    # ----- close database
    #
    db.close()




