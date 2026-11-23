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

from algaeapp import algaeApp
from algaedb import algaeDB

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

import qgisstyle as qgisstyle
from slateapp import slateApp

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Add categoory codes to a shapefile.')
parser.add_argument("input_file", help="Input shapefile.")
parser.add_argument("output_file", help="Output shapefile.")
# parser.add_argument("output_legend_file", help="Output legend file.")
parser.add_argument("-af", "--attribute_field", required=True, default=None, help="Attribute field.")
# parser.add_argument("-sf", "--style_file", default=None, help="Optional name of a QGIS style file that contains styling for the categorical value.")
parser.add_argument("-ot", "--output_type", default="Byte", help="Output raster type, used to check the number of unique values.")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

#
# ----- global variables
#
style_list = []

def read_styles(filename):
#------------------------------------------------------------------------------
    """
    """
    #
    # ----- read style file
    #
    tree = ET.parse(filename)
    root = tree.getroot()
    
    #
    # ----- read, parse, and save categories (symbols) from the style file
    #
    categories = root.find('renderer-v2').find('categories')
    for category in categories.findall('category'):
        style = qgisstyle.QgisStyle()
        style.numeric_code = int(category.get('symbol'))
        style.character_code = category.get('value')
        style.description = category.get('label')
        if args.verbose: print(u"style.description = %s" % style.description)
        style_list.append(style)
    print("%r categorie(s) parsed from the style file." % len(style_list))
    
    #
    # ----- read, parse, and add the color to each style
    #
    symbols = root.find('renderer-v2').find('symbols')
    num_symbols = 0
    for symbol in symbols.findall('symbol'):
        number = int(symbol.get('name'))
        # print("Found symbol color for name %r" % number
        layer = symbol.find('layer')
        for prop in layer.findall('prop'):
            name = prop.get('k')
            value = prop.get('v')
            parts = value.split(',')
            if name == 'color':
                if len(parts) == 4 and number < len(style_list):
                    style_list[number].red = int(parts[0])
                    style_list[number].green = int(parts[1])
                    style_list[number].blue = int(parts[2])
                    style_list[number].alpha = int(parts[3])
        num_symbols += 1
    print("%r symbol(s) parsed from the style file." % num_symbols)
    
    contains_zero_category = False
    for style in style_list:
        if style.numeric_code == 0: contains_zero_category = True
    if contains_zero_category:
        for style in style_list:
            style.numeric_code += 1
            
def create_legend(filename, codes_list, styles_list):
#------------------------------------------------------------------------------
    """
    Create a JSON legend file.
    """
    legend = {}
    legend['legend'] = []
    #
    # ----- default style to use when no matches in a style file, or there is not style file
    #
    default_style = qgisstyle.QgisStyle()
    default_style.red = 1
    default_style.green = 1
    default_style.blue = 1
    default_style.alpha = 1
    #
    # ----- open legend file to write to
    #
    leg = open(filename, 'w')
    if leg != None:
        for code in codes_list:
            pixel_value = unique.index(code) + 1
            #
            # ----- create legend from matches in the style file
            #
            if len(styles_list) > 0:
                style_index = qgisstyle.QgisStyle.find_style_in_list(style_list, code)
                if style_index is not None:
                    styles_list[style_index].numeric_code = pixel_value
                    legend['legend'].append(styles_list[style_index].get_dictionary())
                else:
                    default_style.character_code = code
                    default_style.numeric_code = pixel_value
                    legend['legend'].append(default_style.get_dictionary())
            else:
                #
                # ----- no style file just assign a random color
                #
                default_style.character_code = code
                default_style.numeric_code = pixel_value
                default_style.create_random_color()
                legend['legend'].append(default_style.get_dictionary())
                
        json.dump(legend, leg, indent=2)              
        leg = None
            
    else:
        print('ERROR: Unable to create the legend file ' + filename + '.')
    

#==============================================================================
# Application start
#
#==============================================================================

#
# ----- read input shapefile
#
r = shapefile.Reader(args.input_file)
if r == None:
    sys.exit("ERROR: Unable to open the file %r" + args.input_file)

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
print("Read %r shapes from %s." % (len(polygons), args.input_file))

if args.verbose: print(r.fields)

#
# ----- find the attribute field definition
#
code_field = None    
for field in r.fields:
    if field[0] == args.attribute_field:
        code_field = field

#
# ----- attribute field not found
#
if code_field == None: sys.exit("ERROR: Attribute field %r not found in the shapefile.  Field names are case sensitive." % args.attribute_field)

if args.verbose: print(code_field)

#
# ----- check for and read a style file if it exists
#
if args.style_file != None and args.style_file != 'NONE' and args.style_file != 'null':
    read_styles(args.style_file)

#
# ----- setup output shapefile
#
w = shapefile.Writer(args.output_file, shapeType=r.shapeType)
w.field(code_field[0], code_field[1], code_field[2], code_field[3])
w.field('slate_code', 'N')

#
# ----- get unique sorted list of attribute values
#
unique = []
for shaperec in r.iterShapeRecords():
    strval = str(shaperec.record[args.attribute_field])
    if strval not in unique:
        unique.append(strval)
unique.sort()
        
print("%r unique values found for field %r." % (len(unique), args.attribute_field))
if not slateApp.in_gdal_unit_range(args.output_type, len(unique)):
    print("ERROR: Number of unique values is greater than the " + args.output_type + " output data range.")

#
# ----- check for codes not in the style list
#
if len(style_list) > 0:
    for code in unique:
        if qgisstyle.QgisStyle.find_style_in_list(style_list, code) is None:
            print('WARNING: ' + code + ' not found in the style file.')

#
# ----- loop and create output shapefile
#
for shaperec in r.iterShapeRecords():
    w.record(shaperec.record[args.attribute_field], unique.index(str(shaperec.record[args.attribute_field])) + 1)
    w.shape(shaperec.shape)
    
#
# ----- create the legend file
#
# create_legend(args.output_legend_file, unique, style_list)

#
# ----- close applicable files
#
w.close()

#
# ----- copy the projection file if it exists
#
input_prj_filename = os.path.splitext(args.input_file)[0] + '.prj'

if os.path.isfile(input_prj_filename):
    output_prj_filename = os.path.splitext(args.output_file)[0] + '.prj'
    copyfile(input_prj_filename, output_prj_filename)













