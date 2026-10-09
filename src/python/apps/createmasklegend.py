#!/usr/bin/python3

"""

 slate | Create a legend file for a mask.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import argparse
import json

from algaeapp import algaeApp
from algaedb import algaeDB

sys.path.append('../modules')

from slateapp import slateApp

from qgisstyle import qgisStyle

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Create a legend file for a mask.')
parser.add_argument("output_legend_file", help="Output legend file.")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

#
# ----- open legend file to write to
#
leg = open(args.output_legend_file, 'w')
if leg != None:
    
    legend = {}
    legend['legend'] = []
    
    legend_entry = qgisStyle()
    
    legend_entry.numeric_code = 1
    legend_entry.character_code = 'Data'
    legend_entry.red = 231
    legend_entry.green = 226
    legend_entry.blue = 214
    legend_entry.alpha = 1
    legend['legend'].append(legend_entry.get_dictionary())
    
    legend_entry.numeric_code = slateApp.NODATA_BYTE
    legend_entry.character_code = slateApp.NODATA_DESC
    legend_entry.red = slateApp.NODATA_COLOR[0]
    legend_entry.green = slateApp.NODATA_COLOR[1]
    legend_entry.blue = slateApp.NODATA_COLOR[2]
    legend_entry.alpha = slateApp.NODATA_COLOR[3]
    legend['legend'].append(legend_entry.get_dictionary())
    
    json.dump(legend, leg, indent=2)  
    
    leg.close()