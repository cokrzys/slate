#!/usr/bin/python3

"""

 slate | Parse space delimited codes from a shapefile field.
 
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

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Parse space delimited codes from a shapefile field.')
parser.add_argument("shapefile", help="Input shapefile.")
parser.add_argument("field", help="Attribute field with values to parse.")
parser.add_argument("-rf", "--report_format", default="Basic", help="Report formats (Basic, JSON).")
parser.add_argument("-min_cc", "--min_count_cutoff", type=int, default=0, help="Only report results with a count >= the min cutoff.")
parser.add_argument("-max_cc", "--max_count_cutoff", type=int, default=10000000, help="Only report results with a count <= the max cutoff.")
parser.add_argument("-nt", "--name_template", default="Code {code}, n = {num}", help="Template to build a readable name for each result.")
parser.add_argument("-ft", "--filename_template", default="layer_{code}", help="Filename template.")
parser.add_argument("-not", "--notes_template", default="{num} occurrence(s) of {code} in field {field} from shapefile {shapefile}.", help="Notes template.")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

#
# ----- read input shapefile
#
r = shapefile.Reader(args.shapefile)
if r == None:
    sys.exit("ERROR: Unable to open the file %r" + args.shapefile)

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
print("Read %r shapes from %s." % (len(shapes), args.shapefile))

if args.verbose: print(r.fields)

#
# ----- find the attribute field definition
#
code_field = None    
for field in r.fields:
    if field[0] == args.field:
        code_field = field

#
# ----- attribute field not found
#
if code_field == None: sys.exit("ERROR: Code field %r not found in the shapefile.  Field names are case sensitive." % args.field)

if args.verbose: print(code_field)

#
# ----- get unique sorted list of codes
#
unique_codes = {}
for shaperec in r.iterShapeRecords():
    strval = str(shaperec.record[args.field])
    parts = strval.split(' ')
    for part in parts:
        unique_codes[part] = unique_codes.get(part, 0) + 1
        
# sort by key
# unique_codes = dict(sorted(unique_codes.items()))

# sort by value (count) descending
# can also change item[1] to item[0] to sort by key
unique_codes = dict(sorted(unique_codes.items(), key=lambda item: item[1], reverse=True))
        
print("%r unique codes found for field %r." % (len(unique_codes), args.field))

#
# ----- report results
#
if args.report_format.upper() == 'JSON':
    data_list = list()
    for attribute, value in unique_codes.items():
        if value >= args.min_count_cutoff and value <= args.max_count_cutoff:
            data_dict = dict()
            #
            # ----- build a filter to select data using OGR SQL syntax, for example:
            #       "CODE_LIST LIKE 'U%' OR CODE_LIST LIKE '% U' OR CODE_LIST LIKE '% U %'"
            #
            where_clause = args.field + " ILIKE '" + attribute + " %'"
            where_clause += ' OR ' + args.field + " ILIKE '% " + attribute + "'"
            where_clause += ' OR ' + args.field + " ILIKE '% " + attribute + " %'"
            where_clause += ' OR ' + args.field + " = '" + attribute + "'"
            #
            # ----- add to data dictionary
            #
            data_dict['attribute'] = attribute
            data_dict['num'] = value
            data_dict['name'] = args.name_template.replace('{code}', attribute).replace('{num}', str(value))
            data_dict['filename'] = args.filename_template.replace('{code}', attribute).replace('{num}', str(value)).lower()
            data_dict['filter'] = where_clause
            notes = args.notes_template.replace('{code}', attribute)
            notes = notes.replace('{num}', str(value))
            notes = notes.replace('{field}', args.field)
            notes = notes.replace('{shapefile}', args.shapefile)
            data_dict['notes'] = notes
            data_list.append(data_dict)
    print(json.dumps(data_list, indent=2))
else:
    for attribute, value in unique_codes.items():
        if value >= args.min_count_cutoff and value <= args.max_count_cutoff:
            print('%r %r' % (attribute, value))

