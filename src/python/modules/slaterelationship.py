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
    CELL_COUNT = 'cell_count'
    CELL_COUNT_PCT = 'cell_count_pct'
    SIG_COUNT = 'sig_count'
    SIG_COUNT_PCT = 'sig_count_pct'
    DATA_SUM = 'data_sum'
    DATA_SUM_PCT = 'data_sum_pct'
    
    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.bin_data = []
        self.total_cell_count = None
        self.total_sig_count = None
        self.total_data_sum = None
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
                bin[slateRelationship.MIN] = row[4]
                bin[slateRelationship.MAX] = row[5]
                bin[slateRelationship.CELL_COUNT] = row[1]
                bin[slateRelationship.CELL_COUNT_PCT] = row[6]
                bin[slateRelationship.SIG_COUNT] = 0
                bin[slateRelationship.SIG_COUNT_PCT] = 0
                bin[slateRelationship.DATA_SUM] = 0
                bin[slateRelationship.DATA_SUM_PCT] = 0
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
        for index, bin in enumerate(self.bin_data):
            if (sig >= bin[slateRelationship.MIN] and sig < bin[slateRelationship.MAX]) or \
                (index == len(self.bin_data) - 1 and sig >= bin[slateRelationship.MIN] and sig <= bin[slateRelationship.MAX]):
                bin[slateRelationship.SIG_COUNT] += 1
                if data != None:
                    bin[slateRelationship.DATA_SUM] += data
                #
                #
                #
                if self.total_sig_count != None and self.total_sig_count > 0:
                    bin[slateRelationship.SIG_COUNT_PCT] = (float(bin[slateRelationship.SIG_COUNT]) / float(self.total_sig_count)) * 100
                #
                #
                #
                if self.debug: 
                    print('Bin sig_count is %r.' % bin[slateRelationship.SIG_COUNT])
                    print('Bin data_sum is %r.' % bin[slateRelationship.DATA_SUM])
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
            cumulative_cell_count_pct += float(bin[slateRelationship.CELL_COUNT_PCT])
            cumulative_data_count_pct += float(bin[slateRelationship.SIG_COUNT_PCT])
            contrast = cumulative_data_count_pct - cumulative_cell_count_pct
            if max_contrast == None or contrast > max_contrast:
                max_contrast = contrast
                max_contrast_index = index
                max_contrast_data_pct = cumulative_data_count_pct
                max_contrast_cell_pct = cumulative_cell_count_pct
        if max_contrast != None:
            print('Max cumulative contrast = %r' % max_contrast)
            print('Signature Pct / Cell Pct ratio = %r' % (float(max_contrast_data_pct) / float(max_contrast_cell_pct)))
            print('  Min %r to Max %r' % (self.bin_data[max_contrast_index][slateRelationship.MIN], self.bin_data[max_contrast_index][slateRelationship.MAX]))
            print('  Data %r pct in %r pct of the area.' % (max_contrast_data_pct, max_contrast_cell_pct))
        
        
        
        