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
    
    MIN = 'min'
    MAX = 'max'
    NUM_CELLS = 'num_cells'
    NUM_SIGNATURES = 'num_signatures'
    QUANTITY_SUM = 'quantity_sum'
    
    CONTRAST = 'Contrast'
    PCT_RATIO = 'Pct Ratio'
    
    
    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.bin_data = []
        self.total_num_cells = 0
        self.total_num_signatures = 0
        self.total_quantity = 0
        self.debug = False
        
    def add_histogram(self, histogram):
    #------------------------------------------------------------------------------
        """
        bin, num, cumulative_num, total_num, bin_min, bin_max, pct, cumulative_pct
        """
        if len(histogram) > 0:
            self.total_num_cells = 0
            for row in histogram:
                self.total_num_cells += row[1]
                bin = dict()
                bin[slateRelationship.MIN] = float(row[4])
                bin[slateRelationship.MAX] = float(row[5])
                bin[slateRelationship.NUM_CELLS] = float(row[1])
                bin[slateRelationship.NUM_SIGNATURES] = 0
                bin[slateRelationship.QUANTITY_SUM] = 0
                if len(self.bin_data) == 0 or bin[slateRelationship.MIN] >= self.bin_data[-1][slateRelationship.MAX]:
                    self.bin_data.append(bin)
                else:
                    algaeApp.error_message('Bin min not >= last bin max.')
            if self.debug: print('%r items added to relationship.bin_data.' % len(self.bin_data))
        else:
            algaeApp.error_message('No data for source histogram in add_histogram().')
            
    def add_data_to_bin(self, sig, data = None):
    #------------------------------------------------------------------------------
        """
        """
        self.total_num_signatures += 1
        if data != None: self.total_quantity += data
        #
        #
        #
        for index, bin in enumerate(self.bin_data):
            if (sig >= bin[slateRelationship.MIN] and sig < bin[slateRelationship.MAX]) or \
                (index == len(self.bin_data) - 1 and sig >= bin[slateRelationship.MIN] and sig <= bin[slateRelationship.MAX]):
                bin[slateRelationship.NUM_SIGNATURES] += 1     
                if data != None:
                    bin[slateRelationship.QUANTITY_SUM] += data
                #
                #
                #
                if self.debug: 
                    print('Bin num_signatures is %r.' % bin[slateRelationship.NUM_SIGNATURES])
                    print('Bin quantity_sum is %r.' % bin[slateRelationship.QUANTITY_SUM])
                break
        # TODO: Message if not binned
            
    def calc_cumulative_relationship(self, relationship_type, statistic, reverse = False):
    #------------------------------------------------------------------------------
        """
        """
        num_bins = len(self.bin_data)
        cumulative_amount = 0
        cumulative_cell_count = 0
        max_stat = None
        max_index = None
        max_amount_pct = None
        max_cells_pct = None
        
        r = range(0, num_bins)
        if reverse: r = range(num_bins - 1, -1, -1)
        
        for i in r:
            cumulative_amount += self.bin_data[i][relationship_type]
            cumulative_cell_count += self.bin_data[i][slateRelationship.NUM_CELLS]
            #
            #
            #
            if relationship_type == slateRelationship.NUM_SIGNATURES:
                amount_pct = cumulative_amount / self.total_num_signatures * 100
            else:
                amount_pct = cumulative_amount / self.total_quantity * 100
            cells_pct = cumulative_cell_count / self.total_num_cells * 100
            #
            #
            #
            if statistic == slateRelationship.CONTRAST:
                stat = amount_pct - cells_pct
            elif statistic == slateRelationship.PCT_RATIO:
                stat = amount_pct / cells_pct
            #
            #
            #
            if max_stat == None or stat > max_stat:
                max_stat = stat
                max_index = i
                max_amount_pct = amount_pct
                max_cells_pct = cells_pct
                
        if max_stat != None:
            print('Relationship type ' + relationship_type)
            print(statistic + ' = ' + str(max_stat))
            print('Reverse %r' % reverse)
            
        
        
        
        
        