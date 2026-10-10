#!/usr/bin/python3

"""

 slate | Color an image and make classes with a legend file.
 
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

from algaeapp import algaeApp
from algaedb import algaeDB

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp
from slatelayer import slateLayer
from slateclass import slateClass
from slatelegend import slateLegend

import math
import argparse

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Color an image and make classes with a legend file.')
parser.add_argument("src_dataset", help="The source dataset name.")
parser.add_argument("dst_dataset", help="The destination filename.")
parser.add_argument("legend_filename", help="Legend filename.")
parser.add_argument("--legend_rowid", type=int, default=0, help="Legend rowid from sp.legend.")
parser.add_argument("-lrowid", "--layer_rowid_fk", type=int, default=0, help="Layer rowid.")
parser.add_argument("-hl", "--header_lines", type=int, default=0, help="The number of header lines to skip.")
parser.add_argument("-a_nodata", "--nodata_value", type=int, default=255, help="Nodata value, used to color values.")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

def write_classes(db, layer_rowid_fk, legend, bin_count_tuple, nodata_value):
#------------------------------------------------------------------------------
    """
    Write classes to the database.
    """
    sl = slateLayer()
    sc = slateClass()
    #
    # ----- delete existing classes
    #
    sc.delete_classes(db, layer_rowid_fk)
    num_existing_classes = sc.get_num_classes(db, layer_rowid_fk)
    #
    # ----- continue if there are no classes
    #
    if num_existing_classes == None or num_existing_classes == 0:
        #
        # ----- loop and write each class
        #
        for i in range(0, len(bin_count_tuple[0])):
            sc.layer_rowid_fk = layer_rowid_fk
            sc.raster_value = int(bin_count_tuple[0][i])
            sc.num_values = int(bin_count_tuple[1][i])
            
            matched = False
            i = 0
            if legend != None:
                while not matched and i < len(legend['legend']):
                    if legend['legend'][i]['numCode'] == sc.raster_value:
                        matched = True
                    else:
                        i += 1
            
            if matched:
                sc.code = legend['legend'][i]['charCode']
                sc.html_color = slateApp.rgb_to_html(legend['legend'][i]['red'], legend['legend'][i]['green'], legend['legend'][i]['blue'])
                sc.description = legend['legend'][i]['description']
                sc.write_to_database(db)
            elif sc.raster_value == nodata_value:
                sc.code = slateApp.NODATA_DESC
                sc.description = 'NoData'
                sc.html_color = slateApp.rgb_to_html(slateApp.NODATA_COLOR[0], slateApp.NODATA_COLOR[1], slateApp.NODATA_COLOR[2])
                sc.write_to_database(db)
            else:
                sc.code = str(sc.raster_value)
                sc.html_color = slateApp.get_random_html_color()
                sc.write_to_database(db)
                print('WARNING: Legend not found for raster value %r.' % sc.raster_value)
                
def create_legend(bin_counts):
#------------------------------------------------------------------------------
    """
    """
    legend = { "legend":[] }
    # legend['legend'].append({"numCode":1})
    # legend['legend'].append({"numCode":2})
    
    for i in range(0, len(bin_counts[0])):
        d = dict()
        d['numCode'] = int(bin_counts[0][i])
        d['charCode'] = str(bin_counts[0][i])
        color = slateApp.get_random_rgb_color()
        d['red'] = color[0]
        d['green'] = color[1]
        d['blue'] = color[2]
        d['description'] = ''
        legend['legend'].append(d)
        
    # nested_json = json.dumps(legend, indent=2)
    # print(nested_json)
    
    return legend
    

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
    # ----- open the input file
    #
    input = gdal.Open(args.src_dataset)
    if input is None:
        print('Cannot open raster ' + args.src_dataset)
        sys.exit()
    if args.verbose: print('Input raster opened.')
    
    #
    # ----- input raster parameters used for processing
    #
    band = input.GetRasterBand(1)
    if args.verbose:
        print('band.XSize = %r.' % band.XSize)
        print('band.YSize = %r.' % band.YSize)
    
    #
    # ----- read data
    #
    data = band.ReadAsArray(0, 0, band.XSize, band.YSize)
    gdaltype = slateApp.NP2GDAL_CONVERSION[data.dtype.name]
    if args.verbose: print('GDAL type for the input raster = %r' % gdaltype)
    
    if gdaltype <= 2:
        
        #
        # ----- get bin counts for each value in the raster
        #
        bc = slateApp.get_bin_counts(data)
        if args.verbose: slateApp.report_bin_counts(bc)
        #
        # ----- legend from file
        #
        legend = None
        if args.legend_filename != 'null':
            leg = open(args.legend_filename, 'r')
            if leg is None: sys.exit("ERROR: Unable to open the legend file " + args.legend_filename + ".")
            legend = json.load(leg)
            leg.close()
        #
        # ----- legend from database
        #
        if legend == None and args.legend_rowid > 0:
            print('TODO: Use database legend for colors, legend rowid = ' + str(args.legend_rowid))
        #
        # ----- create random legend
        #
        if legend == None:
            legend = create_legend(bc)
        
        #
        # ----- open xml file
        #
        xml_filename = args.dst_dataset + '.xml'
        xml = open(xml_filename, 'w')
        if xml is None: sys.exit("ERROR: Unable to open the XML file " + xml_filename + ".")
        
        #
        # ----- start the xml header
        #
        xml.write(u'<PAMDataset>\n')
        xml.write(u'\t<PAMRasterBand band="1">\n')
        xml.write(u'\t\t<CategoryNames>\n')
        
        #
        # ----- read and parse the legend file
        #
        colors = gdal.ColorTable()
        
        line_num = 1
        expected_code = 0
        if legend != None:
            for legend_item in legend['legend']:
                if args.verbose: print(u"class [%r] legend [%s] color [%s]" % (code, legend_entry, color))
                #
                # ----- set the color palette entry in the image
                #
                if 'alpha' in legend_item:
                    # TODO: Setting alpha causes no problems but also doesn't seem to work
                    colors.SetColorEntry(legend_item['numCode'], (legend_item['red'], legend_item['green'], legend_item['blue'], legend_item['alpha']))
                else:
                    colors.SetColorEntry(legend_item['numCode'], (legend_item['red'], legend_item['green'], legend_item['blue']))
                #
                # ----- the xml file has to be in sequential order
                #
                while expected_code < legend_item['numCode']:
                    xml.write(u'\t\t<Category>Not Used</Category>\n')
                    expected_code += 1
                #
                # ----- write the xml legend item
                #
                if expected_code == legend_item['numCode']:
                    xml.write(u'\t\t<Category>{name} {desc}</Category>\n'.format(name=legend_item['charCode'], desc=legend_item['description']))
                    expected_code += 1
                elif expected_code > legend_item['numCode']:
                    if args.verbose: print(u"Expected code {ec} is > than the current code {cc}.".format(ec=expected_code, cc=legend_item['numCode']))
        
        #
        # ----- create output raster
        #
        driver = gdal.GetDriverByName("GTiff")
        output = driver.Create(args.dst_dataset, band.XSize, band.YSize, 1, gdaltype)
        if output is None:
            print('ERROR: Unable to create raster ' + args.dst_dataset)
            sys.exit()
        if args.verbose: print('Output raster opened.')
        
        #
        # ----- set output projection to the same as the input
        #
        output.SetGeoTransform(input.GetGeoTransform())
        output.SetProjection(input.GetProjection())
        
        #
        # ----- set the color palette
        #
        output.GetRasterBand(1).SetColorTable(colors)
        output.GetRasterBand(1).SetRasterColorInterpretation(gdal.GCI_PaletteIndex)
        
        #
        # ----- write raster
        #
        output.GetRasterBand(1).SetNoDataValue(band.GetNoDataValue())
        output.GetRasterBand(1).WriteArray(data)
        
        #
        # ----- close the xml header
        #
        xml.write(u'\t\t</CategoryNames>\n')
        xml.write(u'\t</PAMRasterBand>\n')
        xml.write(u'</PAMDataset>\n')
        
        #
        # ----- write classes to the database
        #
        if int(args.layer_rowid_fk) > 0: 
            # print('DEBUG: Before write_classes().')
            write_classes(db, args.layer_rowid_fk, legend, bc, args.nodata_value)
            #
            # ----- TODO: temporary to add legend entries
            #
            if args.legend_rowid > 0:
                # print('DEBUG: Before update_descriptions().')
                sl = slateLegend()
                # sl.debug = True
                sl.update_descriptions(db, args.layer_rowid_fk, args.legend_rowid)
        
        output = None
        xml = None
    else:
        print('ERROR: Only Byte or UInt16 TIFF files support color tables.')
        
    input = None
    
    #
    # ----- close database
    #
    db.close()




