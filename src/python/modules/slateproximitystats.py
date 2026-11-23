"""

 Slate | Proximity stats support.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import math

import numpy as np
from osgeo import gdal, osr

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase

from slateapp import slateApp
from slatelayer import slateLayer
from email.policy import default

class slateProximityStats():

    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        
    def sequential_histograms(self, data):
    #------------------------------------------------------------------------------
        """
        """
        methods = ['auto', 'fd', 'doane', 'scott', 'stone', 'rice', 'sturges', 'sqrt']
        print('Suggested number of bins by method:')
        for method in methods:
            test_edges = np.histogram_bin_edges(data, bins=method)
            print(method + ' = ' + str(len(test_edges)))
        #
        #
        #
        default_method = 'stone'
        print('Using ' + default_method + ' method to calculate the optimal number of bins.')
        return np.histogram(data, bins=default_method)
        
    def run(self, db, args):
    #------------------------------------------------------------------------------
        """
        """
        l = slateLayer()
        l.read_row_from_database_with_rowid(db, args.layer_rowid_fk)
        l.read_data()
        
        print('Calculating histogram for ' + l.filename)
        
        print("NoData value = %r" % l.nodata_value)
        num_nodata_values = (l.data == l.nodata_value).sum()
        print("Num NoData values = %r" % num_nodata_values)
        
        if num_nodata_values > 0:
            l.data = l.data[(l.data != l.nodata_value)]
            
        min = np.nanmin(l.data)
        max = np.nanmax(l.data)
        hist = []
        bin_edges = None
        
        if l.geoprocess.data_distribution.name == 'Proximity':
            #
            # ----- half the cell size + a small buffer
            #
            num_bins = math.ceil(((max - min) / l.resolution.cell_size_x) * 2.25)
            print('Optimal Num Bins = ' + f"{num_bins:.2f}")
            hist, bin_edges = np.histogram(l.data, num_bins)
        elif l.geoprocess.data_distribution.name == 'Sequential':
            #
            #
            #
            hist, bin_edges = self.sequential_histograms(l.data)
        else:
            print('Unsupported distribution type ' + l.geoprocess.data_distribution.name + '.')
        
        if len(hist) > 0:
            if args.debug:
                print('Bin Edges:', bin_edges)
                print('Histogram Counts:', hist)
                print(hist.sum())
                for edge in bin_edges:
                    print(f"{edge:.2f}")
                
            if l.write_histogram(db, hist.tolist(), bin_edges.tolist()):
                print('Histogram and bin edges written to ' + l.table_name + '.')
                if args.debug: l.print_histogram(db)
            
        
        
    