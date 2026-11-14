"""

 Slate | Relationship between a point set and a layer.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import math
import subprocess
import datetime

import numpy as np

from algaecore import algaeCore
from algaeapp import algaeApp

from slateapp import slateApp

class slateRelationship:
    
    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.bin_data = []
        self.total_cells = None
        self.total_data_items = None
        self.debug = False
        
    def add_histogram(self, histogram):
    #------------------------------------------------------------------------------
        """
        bin, num, cumulative_num, total_num, bin_min, bin_max, pct, cumulative_pct
        """
        if len(histogram) > 0:
            self.total_cells = 0
            for row in histogram:
                self.total_cells += row[1]
                bin = dict()
                bin['min'] = row[4]
                bin['max'] = row[5]
                bin['cell_count'] = row[1]
                bin['cell_count_pct'] = row[6]
                bin['data_count'] = 0
                bin['data_count_pct'] = 0
                if len(self.bin_data) == 0 or bin['min'] >= self.bin_data[-1]['max']:
                    self.bin_data.append(bin)
                else:
                    algaeApp.error_message('Bin min not >= last bin max.')
            if self.debug: print('%r items added to relationship.bin_data.' % len(self.bin_data))
        else:
            algaeApp.error_message('No data for source histogram in add_histogram().')
            
    def add_data_to_bin(self, val):
    #------------------------------------------------------------------------------
        """
        """
        for bin in self.bin_data:
            if val >= bin['min'] and val < bin['max']:
                bin['data_count'] += 1
                if self.total_data_items != None and self.total_data_items > 0:
                    bin['data_count_pct'] = (float(bin['data_count']) / float(self.total_data_items)) * 100
                if self.debug: print('Bin data_count is %r.' % bin['data_count'])
                break
            
    def print_max_contrast(self):
    #------------------------------------------------------------------------------
        """
        """
        max_contrast = None
        max_contrast_index = None
        for index, bin in enumerate(self.bin_data):
            contrast = float(bin['data_count_pct']) - float(bin['cell_count_pct'])
            if max_contrast == None or contrast > max_contrast:
                max_contrast = contrast
                max_contrast_index = index
        if max_contrast != None:
            print('Max contrast = %r' % max_contrast)
            
    def max_contrast(self):
    #------------------------------------------------------------------------------
        """
        """
        max_contrast = None
        max_contrast_index = None
        max_contrast_data_pct = None
        max_contrast_cell_pct = None
        cumulative_cell_count_pct = 0.0
        cumulative_data_count_pct = 0.0
        for index, bin in enumerate(self.bin_data):
            cumulative_cell_count_pct += float(bin['cell_count_pct'])
            cumulative_data_count_pct += float(bin['data_count_pct'])
            contrast = cumulative_data_count_pct - cumulative_cell_count_pct
            if max_contrast == None or contrast > max_contrast:
                max_contrast = contrast
                max_contrast_index = index
                max_contrast_data_pct = cumulative_data_count_pct
                max_contrast_cell_pct = cumulative_cell_count_pct
        if max_contrast != None:
            print('Max cumulative contrast = %r' % max_contrast)
            print('  Min %r to Max %r' % (self.bin_data[max_contrast_index]['min'], self.bin_data[max_contrast_index]['max']))
            print('  Data %r pct in %r pct of the area.' % (max_contrast_data_pct, max_contrast_cell_pct))
        
        
        
        