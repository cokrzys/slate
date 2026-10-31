#!/usr/bin/python3

"""

 slate | Mask a raster using a second raster and specified values.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

from osgeo import gdal
import sys
import numpy as np
import math
import argparse
import builtins

from algaecore import algaeCore
from algaeapp import algaeApp

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp

app = slateApp()
builtins.app = app # add app to builtins for true globl access
gdal.DontUseExceptions() # prevents warning re: future exceptions use by default

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Mask a raster using a second raster and specified values.')
parser.add_argument("mask_dataset", help="The mask dataset name.")
parser.add_argument("src_dataset", help="The source dataset name.")
parser.add_argument("dst_dataset", help="The destination filename.")
parser.add_argument("-mv", "--mask_value", default="NoData", help="The value in the mask to select cells in the destination to replace.")
parser.add_argument("-ov", "--output_value", default="NoData", help="The value to place in the destination for cells selected in the mask.")
parser.add_argument("-nodata", "--nodata_value", default="NoData", help="The output nodata value, when not specified the input nodata value is used.")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

#
# ----- show args
#
if args.verbose:
    print("args.mask_dataset = %s" % args.mask_dataset)
    print("args.src_dataset = %s" % args.src_dataset)
    print("args.dst_dataset = %s" %args.dst_dataset)
    print("args.mask_value = %r" %args.mask_value)
    print("args.output_value = %r" %args.output_value)
    print("args.nodata_value = %r" %args.nodata_value)
    print("args.verbose = %r" % args.verbose)

#
# ----- open the mask
#
mask = gdal.Open(args.mask_dataset)
if mask is None:
    print('ERROR: Cannot open raster ' + args.mask_dataset)
    sys.exit()
if args.verbose: print('Mask raster opened.')
mask_band = mask.GetRasterBand(1)

#
# ----- open the input file
#
input = gdal.Open(args.src_dataset)
if input is None:
    print('ERROR: Cannot open raster ' + args.src_dataset)
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
# ----- rasters must have the same dimensions
#
if mask_band.XSize != band.XSize or mask_band.YSize != band.YSize:
    print('ERROR: Mask and input raster are not the same size.')
    sys.exit()

#
# ----- read mask data and input data
#
mask_data = mask_band.ReadAsArray(0, 0, mask_band.XSize, mask_band.YSize)
data = band.ReadAsArray(0, 0, band.XSize, band.YSize)
gdaltype = slateApp.NP2GDAL_CONVERSION[data.dtype.name]
if args.verbose: print('GDAL type for the input raster = %r' % gdaltype)

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
# ----- mask and output values
#
mask_value = args.mask_value
if args.mask_value == 'NoData': mask_value = mask_band.GetNoDataValue()
output_value = args.output_value
if args.output_value == 'NoData': output_value = mask_band.GetNoDataValue()

#
#
#
nodata_value = slateApp.NODATA_BYTE
if data.dtype == 'float32': nodata_value = slateApp.NODATA_FLOAT32
if args.nodata_value == 'NoData' and band.GetNoDataValue():
    nodata_value = band.GetNoDataValue()
else:
    nodata_value = float(args.nodata_value)
    
# print('DEBUG: NoData value in mask raster = %r' % mask_value)
# print('DEBUG: Output value = %r' % output_value)
# print('DEBUG: Output NoData value = %r' % nodata_value)
# print('DEBUG: mask_data.dtype.name = %r' % mask_data.dtype.name)
# print('DEBUG: data.dtype.name = %r' % data.dtype.name)
    
#
# ----- type conversions to get the ().sum() items below to work
#
if 'float' in mask_data.dtype.name.lower(): 
    mask_value = float(mask_value)
elif 'int' in mask_data.dtype.name.lower():  
    mask_value = int(mask_value)
else:
    algaeApp.error_message('Mask data type ' + mask_data.dtype.name + ' not recognized to cast nodata value for comparisons.')

if 'float' in data.dtype.name.lower(): 
    output_value = float(output_value)
elif 'int' in data.dtype.name.lower():  
    output_value = int(float(output_value))
else:
    algaeApp.error_message('Output type ' + data.dtype.name + ' not recognized to cast nodata value for comparisons.')

#
# ----- 
#
# print('DEBUG: NoData value in mask raster = %r' % mask_value)
# print('DEBUG: Output value = %r' % output_value)
# print('DEBUG: Output NoData value = %r' % nodata_value)

#
# ----- apply mask values to output grid
#
if args.verbose: print('Checking for mask values.')
print('Mask raster contains %r NoData values = %r.' % ((mask_data == mask_value).sum(), mask_value))
data[(mask_data == mask_value)] = output_value
print('Output raster will contain %r NoData values = %r.' % ((data == output_value).sum(), output_value))

#
# ----- set output projection to the same as the input
#
output.SetGeoTransform(input.GetGeoTransform())
output.SetProjection(input.GetProjection())
if gdaltype <= 2:
    if args.verbose: print('Setting the color palette.')
    output.GetRasterBand(1).SetColorTable(band.GetColorTable())

#
# ----- write raster and close output file
#
output.GetRasterBand(1).SetNoDataValue(nodata_value)
output.GetRasterBand(1).WriteArray(data)
output = None




