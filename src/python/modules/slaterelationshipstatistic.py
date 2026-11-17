"""

  slate | Statistics associated with a relationship and support for sp.relationship_statistic.

  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate
 
"""

import sys
import os
import psycopg2
import json

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase
from algaetbldatavaluebase import algaeTblDataValueBase

from slateconfig import slateConfig
from slateapp import slateApp
from slaterelationship import slateRelationship

class slateRelationshipStatistic(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.relationship_statistic'
        self.relationship = slateRelationship()
        self.name = None
        self.val = None
        self.distribution = None
        self.amount_type = None
        self.bin_index = None
        self.bin_min = None
        self.bin_max = None
        self.amount = None
        self.cells = None
        self.amount_pct = None
        self.cells_pct = None
        self.description = None
        
    def calc_cumulative(self, relationship_type, statistic, reverse = False):
    #------------------------------------------------------------------------------
        """
        """
        num_bins = len(self.relationship.bin_data)
        cumulative_amount = 0
        cumulative_cell_count = 0
        max_stat = None
        max_index = None
        max_amount = None
        max_cells = None
        max_amount_pct = None
        max_cells_pct = None
        
        self.name = statistic
        self.distribution = 'Cumulative Ascending'
        if reverse: self.distribution = 'Cumulative Descending'
        self.amount_type = relationship_type
        
        r = range(0, num_bins)
        if reverse: r = range(num_bins - 1, -1, -1)
        
        for i in r:
            cumulative_amount += self.relationship.bin_data[i][relationship_type]
            cumulative_cell_count += self.relationship.bin_data[i][slateRelationship.CELLS]
            #
            #
            #
            if relationship_type == slateRelationship.SIGNATURES:
                amount_pct = cumulative_amount / self.relationship.total_signatures * 100
            else:
                amount_pct = cumulative_amount / self.relationship.total_quantity * 100
            cells_pct = cumulative_cell_count / self.relationship.total_cells * 100
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
            if self.relationship.debug:
              print('Index %r cum amt %r cum cells %r amt pct %r cells %r stat %r' % 
                    (i, cumulative_amount, cumulative_cell_count, amount_pct, cells_pct, stat))
            #
            #
            #
            if max_stat == None or stat > max_stat:
                max_stat = stat
                max_index = i
                max_amount = cumulative_amount
                max_cells = cumulative_cell_count
                max_amount_pct = amount_pct
                max_cells_pct = cells_pct
                if self.relationship.debug: 'Max stat updated.'
                
        if max_stat != None:
            self.val = max_stat
            self.bin_index = max_index
            self.amount = max_amount
            self.cells = max_cells
            self.amount_pct = max_amount_pct
            self.cells_pct = max_cells_pct
            self.bin_min = self.relationship.bin_data[max_index][slateRelationship.MIN]
            self.bin_max = self.relationship.bin_data[max_index][slateRelationship.MAX]
            print('---')
            print('Relationship type ' + relationship_type)
            print(statistic + ' = ' + str(max_stat))
            print('Reverse %r' % reverse)
            print('Bin min %r max %r' % 
                  (self.relationship.bin_data[max_index][slateRelationship.MIN], self.relationship.bin_data[max_index][slateRelationship.MAX]))
            #
            #
            #
        
        
            
        







