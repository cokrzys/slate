#!/usr/bin/python3

"""

 slate | Raster statistics.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import numpy as np
import scipy
import scipy.stats
import math
import argparse
import time
from numpy import array
from osgeo import gdal
from osgeo import osr
import builtins

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from numpy._core.defchararray import lower

app = algaeApp(False, False)
sys.path.append(app.config.getAppConfigParameter('slate', 'pythonModulesPath'))

from slateapp import slateApp
from slatelayer import slateLayer

#
# ----- start time
#
app_start_time = time.time()

#
# ----- setup command line arguments
#
parser = argparse.ArgumentParser(description='Raster statistics.')
parser.add_argument("grid", help="The name of the grid.")
parser.add_argument("-c", "--count_unique_values", help="Count the unique values.", action="store_true")
parser.add_argument("-cs", "--count_scalar", type=int, default=100, help="A scalar to multiply the grid values by when counting unique values.")
parser.add_argument("-ca", "--count_above", help="Count the number of values above a cutoff.", action="store_true")
parser.add_argument("-ca_cutoff", "--count_above_cutoff", type=float, help="Cutoff to use when counting the values above (> than).")
parser.add_argument("-t", "--run_tests", help="Run statistical tests (can take some time).", action="store_true")
parser.add_argument("-wf", "--wiki_format", help="Include a wiki table format summary of key statistics.", action="store_true")
parser.add_argument("-wfd", "--wiki_format_decimals", type=int, default=3, help="Number of decimals to use for the wiki format summary.")
parser.add_argument("-ccb", "--create_color_breaks", help="Create a color breaks file based on cutoff parameters.", action="store_true")
parser.add_argument("-cu", "--cutoff_units", default='Percentile', help="Cutoff units, Percentile, Absolute, MinMax.")
parser.add_argument("-lc", "--lower_cutoff", default=20, type=int, help="Lower cutoff in cutoff_units.")
parser.add_argument("-uc", "--upper_cutoff", default=80, type=int, help="Upper cutoff in cutoff_units.")
parser.add_argument("-lrowid", "--layer_rowid_fk", default=None, help="Layer rowid must be specified when using the --write_stats option.")
parser.add_argument("-ws", "--write_stats", help="Write stats to the database record for a layer.", action="store_true")
parser.add_argument("-v", "--verbose", help="Verbose messages.", action="store_true")
args = parser.parse_args()

#==============================================================================
# Application start
#
#==============================================================================

app = slateApp()
builtins.app = app # add app to builtins for true globl access
gdal.DontUseExceptions() # prevents warning re: future exceptions use by default
layer = None

db = algaeDB()
if db.open(app.config.app_database, app.config.database_port, app.config.database_username,
           app.config.database_password):
            
     #
     # ----- 
     #
     if args.layer_rowid_fk != None:
         layer = slateLayer()
         layer.read_row_from_database_with_rowid(db, args.layer_rowid_fk, True)
         # args.grid = layer.get_fully_pathed_filename() don't do this, does not support temporary filenames
     
     #
     # ----- open the grid, read the data, generate stats
     #
     input = gdal.Open(args.grid)
     if input != None:
         band = input.GetRasterBand(1)
         data = band.ReadAsArray(0, 0, band.XSize, band.YSize)
         #
         # ----- header
         #
         print("")
         print('-'*80)
         print("Statistics for %s" % args.grid)
         print('-'*80)
         #
         # ----- rows, columns, total cells
         #
         print("Num cols = %r" % band.XSize)
         print("Num rows = %r" % band.YSize)
         print("Total num cells (ncols x nrows) = %r" % (band.XSize * band.YSize))
         num_valid_cells = band.XSize * band.YSize
         print("Data type = %s" % data.dtype)
         num_nodata_values = None
         #
         # ----- nodata values
         #
         if band.GetNoDataValue() != None:
             print("NoData value = %r" % band.GetNoDataValue())
             num_nodata_values = (data == band.GetNoDataValue()).sum()
             print("Num NoData values = %r" % num_nodata_values)
             if num_nodata_values > 0:
                 if data.dtype == 'float32' or data.dtype == 'float64':
                     data[(data == band.GetNoDataValue())] = np.nan
                 else:
                     data = data[(data != band.GetNoDataValue())]
             # num_nodata_values = (data == np.nan).sum() this does not work !!
             num_valid_cells = (band.XSize * band.YSize) - num_nodata_values
             print("Num valid data cells = %r" % (num_valid_cells))
             if num_nodata_values > 0:
                 print("Percent of cells that are NoData = %r" % ((float(num_nodata_values) / float(band.XSize * band.YSize)) * 100.0))
                 print("Percent of valid data cells = %r" % ((float(num_valid_cells) / float(band.XSize * band.YSize)) * 100.0))
         else:
             print("NoData value not set.")
         
         #
         # ----- missing information values
         #
     #     if data.dtype == 'float32' or data.dtype == 'float64':
     #         num_missing_information_values = (data == ProspBase.MISSING_DATA_FLOAT).sum()
     #         print('Num {} values = {}'.format(ProspBase.MISSING_DATA_DESC, num_missing_information_values))
     #         if num_missing_information_values > 0:
     #             data[(data == ProspBase.MISSING_DATA_FLOAT)] = np.nan
         #
         # ----- not present values
         #
     #     if data.dtype == 'float32' or data.dtype == 'float64':
     #         num_not_present_values = (data == ProspBase.NOT_PRESENT_FLOAT).sum()
     #         print('Num {} values = {}'.format(ProspBase.NOT_PRESENT_DESC, num_not_present_values))
     #         if num_not_present_values > 0:
     #             data[(data == ProspBase.NOT_PRESENT_FLOAT)] = np.NaN
         #
         # ----- zero values
         #
         num_zero_values = (data == 0).sum()
         print("Num zero values = %r" % num_zero_values)
         if num_zero_values > 0:
             print("Percent of valid cells that are zero = %r" % ((float(num_zero_values) / float(num_valid_cells)) * 100.0))
         #
         # ----- values above a cutoff
         #
         if args.count_above:
             num_above = (data > args.count_above_cutoff).sum()
             print("Num values > %r = %r" % (args.count_above_cutoff, num_above))
         #
         # ----- min, max, mean, standard deviation
         #
         mean = np.nanmean(data)
         min = np.nanmin(data)
         max = np.nanmax(data)
         standard_deviation = np.nanstd(data)
         print("Minimum = %r" % min)
         print("Maximum = %r" % max)
         print("Mean = %r" % mean)
         print("Standard Deviation = %r" % standard_deviation)
         print("Mean +/- 1sd = %r %r" % ( (mean - (1 * standard_deviation)), (mean + (1 * standard_deviation)) ))
         print("Mean +/- 2sd = %r %r" % ( (mean - (2 * standard_deviation)), (mean + (2 * standard_deviation)) ))
         print("Mean +/- 3sd = %r %r" % ( (mean - (3 * standard_deviation)), (mean + (3 * standard_deviation)) ))
         #
         # ----- quantiles
         #
         q = np.nanquantile(data, [0.25, 0.5, 0.75])
         print('Q1 = %r' % q[0])
         print('Q2 = %r' % q[1])
         print('Q3 = %r' % q[2])
         #
         # ----- percentiles
         #       breakpoints array is like [1,2,3 ... 99] for percentiles at 0.01 through 0.99
         #
         percentile_breaks = [x for x in range(1, 100, 1)]
         p = np.nanpercentile(data, percentile_breaks)
         
#             print('Number of percentile breaks = %r' % len(percentile_breaks))
#             print('Number of percentiles calculated = %r' % len(p))
#             print('Percentile | Value')
#             if len(percentile_breaks) == len(p):
#                 for idx, x in enumerate(percentile_breaks):
#                     print('%r | %r' % (percentile_breaks[idx], p[idx]))
         #
         #
         #
         lower_cutoff = None
         upper_cutoff = None
         if args.cutoff_units == 'Absolute':
             print('Using Absolute (specified value) cutoffs.')
             lower_cutoff = args.lower_cutoff
             upper_cutoff = args.upper_cutoff
         elif args.cutoff_units == 'Percentile':
             print('Using Percentile based cutoffs at %r and %r.' % (args.lower_cutoff, args.upper_cutoff))
             if len(p) == 99:
                 if int(args.lower_cutoff) >= 1 and int(args.lower_cutoff) <= 99:
                     lower_cutoff = p[int(args.lower_cutoff) - 1]
                 else:
                     algaeApp.error_message('Percentile ' + str(args.lower_cutoff) + ' invalid, must be an integer 1-99.')
                 if int(args.upper_cutoff) >= 1 and int(args.upper_cutoff) <= 99 and int(args.upper_cutoff) > int(args.lower_cutoff):
                     upper_cutoff = p[int(args.upper_cutoff) - 1]
                 else:
                     algaeApp.error_message('Percentile ' + str(args.upper_cutoff) + ' invalid, must be an integer 1-99 and larger than the lower cutoff.')
                 
             else:
                 alageApp.error_message('Incomplete percentiles array with ' + str(len(p)) + ' element(s).')
         elif args.cutoff_units == 'MinMax':
             lower_cutoff = min
             upper_cutoff = max
         else:
             algaeApp.error_message('Cutoff units ' + args.cutoff_units + ' not supported.')
         if lower_cutoff == None:
             print('Defaulting lower cutoff to the minimum.')
             lower_cutoff = min
         if upper_cutoff == None:
             print('Defaulting upper cutoff to the maximum.')
             upper_cutoff = max
         print('Lower cutoff = %r' % lower_cutoff)
         print('Upper cutoff = %r' % upper_cutoff)
         #
         #
         #
         if layer != None:
             layer.num_cols = int(band.XSize)
             layer.num_rows = int(band.YSize)
             layer.num_nodata_cells = int(num_nodata_values)
             layer.data_format = str(data.dtype)
             layer.data_min = float(min) # TODO: Adapt for different data types
             layer.data_max = float(max) # TODO: Adapt for different data types
             layer.data_mean = float(mean) # TODO: Adapt for different data types
             layer.data_stddev = float(standard_deviation) # TODO: Adapt for different data types
             layer.data_q1 = float(q[0]) # TODO: Adapt for different data types
             layer.data_median = float(q[1]) # TODO: Adapt for different data types
             layer.data_q3 = float(q[2]) # TODO: Adapt for different data types
             layer.sig_lower_cutoff = float(lower_cutoff)
             layer.sig_upper_cutoff = float(upper_cutoff)
             layer.write_to_database(db)
         #
         # ----- skewness
         #
         valid_data = data[~np.isnan(data)]
         # print("Skewness = %r" % scipy.stats.skew(valid_data))
         #
         # ----- key stats in wiki table format
         #
         if args.wiki_format:
             print("|  {0:<.{1}f}".format(np.nanmin(data), args.wiki_format_decimals))
             print("|  {0:<.{1}f}".format(np.nanmax(data), args.wiki_format_decimals))
             print("|  {0:<.{1}f}".format(mean, args.wiki_format_decimals))
             print("|  {0:<.{1}f}".format(standard_deviation, args.wiki_format_decimals))
             print("|  {0:<.{2}f} {1:<.{2}f}".format((mean - (1 * standard_deviation)), (mean + (1 * standard_deviation)), args.wiki_format_decimals))
             print("|  {0:<.{2}f} {1:<.{2}f}".format((mean - (2 * standard_deviation)), (mean + (2 * standard_deviation)), args.wiki_format_decimals))
             print("|  {0:<.{2}f} {1:<.{2}f}".format((mean - (3 * standard_deviation)), (mean + (3 * standard_deviation)), args.wiki_format_decimals))
         #
         # ----- stats tests
         #
         if args.run_tests:
             print("Skewness test = %r %r" % scipy.stats.skewtest(valid_data))
             print("Kurtosis test = %r %r" % scipy.stats.kurtosistest(valid_data))
             print("Normal test = %r %r" % scipy.stats.normaltest(valid_data))
             print("Shapiro-Wilk = %r %r" % scipy.stats.shapiro(valid_data))
         #
         # ----- count the number of unique values if applicable
         #
         if args.count_unique_values or data.dtype == 'int16':
             bin_count_list = slateApp.get_bin_counts(valid_data, args.count_scalar)
             slateApp.report_bin_counts(bin_count_list, args.count_scalar )
         #
         # ----- create a color breaks file
         #
         if args.create_color_breaks:
             dirname = os.path.dirname(args.grid)
             filename = os.path.join(dirname, 'color_breaks.txt')
             print('Creating color breaks file ' + filename + '.')
             with open(filename, "w") as cbf:
                 
#                     qstr = '{:.{num_decimals}f} '.format(min, num_decimals = 3)
#                     cbf.write(qstr + ' 254 237 222\n')
#                     qstr = '{:.{num_decimals}f} '.format(lower_cutoff, num_decimals = 3)
#                     cbf.write(qstr + ' 253 190 133\n')
#                     qstr = '{:.{num_decimals}f} '.format(upper_cutoff, num_decimals = 3)
#                     cbf.write(qstr + ' 166 54 3\n')
#                     qstr = '{:.{num_decimals}f} '.format(max, num_decimals = 3)
#                     cbf.write(qstr + ' 166 54 3\n')

# 5-class GnBu
#                  qstr = '{:.{num_decimals}f} '.format(min, num_decimals = 3)
#                  cbf.write(qstr + ' 240,249,232\n')
#                  qstr = '{:.{num_decimals}f} '.format(q[0], num_decimals = 3)
#                  cbf.write(qstr + ' 186,228,188\n')
#                  qstr = '{:.{num_decimals}f} '.format(q[1], num_decimals = 3)
#                  cbf.write(qstr + ' 123,204,196\n')
#                  qstr = '{:.{num_decimals}f} '.format(q[2], num_decimals = 3)
#                  cbf.write(qstr + ' 67,162,202\n')
#                  qstr = '{:.{num_decimals}f} '.format(max, num_decimals = 3)
#                  cbf.write(qstr + ' 8,104,172\n')

# 5-class OrRd
#                     qstr = '{:.{num_decimals}f} '.format(min, num_decimals = 3)
#                     cbf.write(qstr + ' 254,240,217\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[0], num_decimals = 3)
#                     cbf.write(qstr + ' 253,204,138\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[1], num_decimals = 3)
#                     cbf.write(qstr + ' 252,141,89\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[2], num_decimals = 3)
#                     cbf.write(qstr + ' 227,74,51\n')
#                     qstr = '{:.{num_decimals}f} '.format(max, num_decimals = 3)
#                     cbf.write(qstr + ' 179,0,0\n')

# 5-class YlGnBu good, subdued
                    qstr = '{:.{num_decimals}f} '.format(min, num_decimals = 3)
                    cbf.write(qstr + ' 255,255,204\n')
                    qstr = '{:.{num_decimals}f} '.format(q[0], num_decimals = 3)
                    cbf.write(qstr + ' 161,218,180\n')
                    qstr = '{:.{num_decimals}f} '.format(q[1], num_decimals = 3)
                    cbf.write(qstr + ' 65,182,196\n')
                    qstr = '{:.{num_decimals}f} '.format(q[2], num_decimals = 3)
                    cbf.write(qstr + ' 44,127,184\n')
                    qstr = '{:.{num_decimals}f} '.format(max, num_decimals = 3)
                    cbf.write(qstr + ' 37,52,148\n')
                 
# 5-class YlOrRd good, bright
#                     qstr = '{:.{num_decimals}f} '.format(min, num_decimals = 3)
#                     cbf.write(qstr + ' 255 255 178\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[0], num_decimals = 3)
#                     cbf.write(qstr + ' 254 204 92\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[1], num_decimals = 3)
#                     cbf.write(qstr + ' 253 141 60\n')
#                     qstr = '{:.{num_decimals}f} '.format(q[2], num_decimals = 3)
#                     cbf.write(qstr + ' 240 59 32\n')
#                     qstr = '{:.{num_decimals}f} '.format(max, num_decimals = 3)
#                     cbf.write(qstr + ' 189 0 38\n')
                 
                    cbf.write('nv 0 0 0 0\n') # to make NoData values transparent, must also use -alpha flag with gdaldem
         #
         # ----- close the grid
         #
         print('-'*80)
         input = None
     else:
         print("Error: Unable to open %s" % args.grid)
         
     #
     # ----- close database
     #
     db.close()

print("")





