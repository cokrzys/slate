"""

 Slate | Quantify relationships between places and layers.
 
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
from algaedb import algaeDB
from algaetblcoreprocess import algaeTblCoreProcess

from slateapp import slateApp
from refresolution import refResolution
from slateplace import slatePlace
from slateplaceset import slatePlaceSet
from slatelayer import slateLayer
from slaterelationship import slateRelationship

class slateQuantifyRelationships:
    
    def __init__(self, db, args):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.db = db
        self.args = args
        self.core_process = None # an instance of algaeTblCoreProcess if it's used
        self.resolution = refResolution()
        
    def read_signatures(self, layer, place_set):
    #------------------------------------------------------------------------------
        """
        """
        sql = u"""SELECT place_rowid_fk, numeric_val
                FROM sp.signature
                WHERE layer_rowid_fk = %(layer_rowid_fk)s AND resolution_rowid_fk = %(resolution_rowid_fk)s
                  AND place_rowid_fk IN ("""
        sql += place_set.sql + ")"
        return self.db.get_all(sql, {'layer_rowid_fk': layer.rowid, 'resolution_rowid_fk': self.resolution.rowid})
        
        
    def process_layer(self, layer, histogram_data, place_set):
    #------------------------------------------------------------------------------
        """
        """
        rel = slateRelationship()
        # rel.debug = True
        signature_data = self.read_signatures(layer, place_set)
        print('%r signature(s) loaded.' % len(signature_data))
        rel.total_data_items = len(signature_data)
        rel.add_histogram(histogram_data)
        if len(signature_data) > 0:
            for row in signature_data:
                rel.add_data_to_bin(row[1])
            rel.print_max_contrast()
            rel.max_contrast()
        
        
    def loop_through_layers(self, place_set):
    #------------------------------------------------------------------------------
        """
        """
        l = slateLayer()
        rowids_array = l.get_layers_for_project(self.db, place_set.project.rowid, self.resolution.rowid)
        if rowids_array != None and len(rowids_array) > 0:
            num_checked = 0
            for rowid in rowids_array:
                l = slateLayer()
                if l.read_row_from_database_with_rowid(self.db, rowid):
                    print('Processing layer ' + l.geoprocess.name + ' (rowid ' + str(l.rowid) + ')')
                    histogram_data = l.read_histogram(self.db)
                    if len(histogram_data) > 0:
                        if self.args.debug: layer.print_histogram(self.db)
                        self.process_layer(l, histogram_data, place_set)
                    else:
                        algaeApp.error_message('Histogram not found for layer ' + l.geoprocess.name + '.')
                num_checked += 1
                if self.args.max_to_run != None and num_checked >= self.args.max_to_run:
                    break
                            
    def run(self):
    #------------------------------------------------------------------------------
        """
        """
        #
        # ----- read resolution
        #
        self.resolution.read_row_from_database_with_rowid(self.db, self.args.resolution_rowid_fk)
        if self.resolution.folder != None and len(self.resolution.folder) > 0:
            print('Running at a resolution of ' + self.resolution.name + '.')
        else:
            algaeApp.error_message('Resolution not setup.')
            return
        #
        #
        #
        ps = slatePlaceSet()
        ps.read_row_from_database_with_rowid(self.db, self.args.place_set_rowid_fk)
        if ps.rowid != None:
            if self.args.debug: print(ps.sql)
            self.loop_through_layers(ps)
        else:
            algaeApp.error_message('Problem reading place set.')
        
        
        
        