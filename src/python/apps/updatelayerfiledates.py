#!/usr/bin/python3

"""

 slate | Update the database with the latest file date(s) for a layer.
 
 Stored dates are used for reporting and to help keep track of which layers are up-to-date.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
from datetime import datetime
import argparse
import builtins

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp
from slatelayer import slateLayer

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Update the database with the latest file date(s) for a layer.')
parser.add_argument("--layer_rowid_fk", type=int, required=True, help="Layer rowid.")
parser.add_argument("--column", required=True, help="Generic name for a column to update.", choices=['begin', 'end'])
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
    print('Updating layer calc ' + args.column + ' date/time.')
    print('Database ' + app.config.app_database + ' opened.')
    #
    # ----- open layer and update date(s)
    #
    l = slateLayer()
    l.read_row_from_database_with_rowid(db, args.layer_rowid_fk, False)
    if l.rowid != None:
        if args.column == 'begin':
            l.update_calc_begin(db)
        elif args.column == 'end':
            l.update_calc_end(db)
        else:
            algaeApp.error_message('Generic column name ' + args.column + ' not supported.')
        """
        epoch = os.path.getmtime(l.get_fully_pathed_filename())            
        ts = datetime.utcfromtimestamp(epoch)
        print(l.get_fully_pathed_filename() + ' last updated ' + ts.strftime('%d-%b-%Y %H:%M:%S') + '.')
        if l.update_last_updated(db, None, epoch):
            print('Database updated.')
        """
        
    else:
        algaeApp.error_message('Unable to read data for layer rowid ' + str(args.layer_rowid_fk), + '.')
    #
    # ----- close database
    #
    db.close()




