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
import inspect

import numpy as np

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblfield import algaeTblField
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
        self.debug = False
        
    def read_signatures(self, layer, place_set):
    #------------------------------------------------------------------------------
        """
        """
        print(inspect.currentframe().f_code.co_name)
        sql = u"""SELECT place_rowid_fk, numeric_val
                FROM sp.signature
                WHERE layer_rowid_fk = %(layer_rowid_fk)s AND resolution_rowid_fk = %(resolution_rowid_fk)s
                  AND place_rowid_fk IN ("""
        sql += place_set.sql + ")"
        parms = {'layer_rowid_fk': layer.rowid, 'resolution_rowid_fk': self.resolution.rowid}
        #
        # ----- if optional field_rowid_fk read data from a value field as well
        #
        if self.args.field_rowid_fk != None:
            field = algaeTblField()
            field.read_row_from_database_with_rowid(self.db, self.args.field_rowid_fk)
            if field.rowid != None:
                if field.isNumeric():
                    sql = "SELECT t1.*, " + field.getDataColumn() + " FROM (" + sql
                    sql +=  u""") t1 LEFT OUTER JOIN sp.place_data pd ON pd.place_rowid_fk = t1.place_rowid_fk
                              AND field_rowid_fk = %(field_rowid_fk)s"""
                    parms['field_rowid_fk'] = self.args.field_rowid_fk
                else:
                    algaeApp.error_message('Value field ' + field.field_type.name + ' is not numeric.')
            else:
                algaeApp.error_message('Problem reading field for rowid ' + str(self.args.field_rowid_fk) + '.')
        return self.db.get_all(sql, parms)
        
        
    def process_layer(self, rowid, place_set):
    #------------------------------------------------------------------------------
        """
        """
        layer = slateLayer()
        rel = slateRelationship()
        rel.debug = self.debug        
        if layer.read_row_from_database_with_rowid(self.db, rowid):
            print('Processing layer ' + layer.geoprocess.name + ' (rowid ' + str(layer.rowid) + ')')
            histogram_data = layer.read_histogram(self.db)
            if len(histogram_data) > 0:
                if self.debug: layer.print_histogram(self.db)
                rel.add_histogram(histogram_data)
                #
                #
                #
                signature_data = self.read_signatures(layer, place_set)
                print('%r signature(s) loaded.' % len(signature_data))
                if len(signature_data) > 0:
                    for row in signature_data:
                        if len(row) == 3:
                            if self.debug: print('Adding sig %r and quantity %r' % (row[1], row[2]))
                            rel.add_data_to_bin(row[1], row[2])
                        else:
                            rel.add_data_to_bin(row[1])
                    #
                    #
                    #
                    rel.calc_cumulative_relationship(slateRelationship.NUM_SIGNATURES, slateRelationship.CONTRAST, False)
                    rel.calc_cumulative_relationship(slateRelationship.NUM_SIGNATURES, slateRelationship.PCT_RATIO, False)
                    rel.calc_cumulative_relationship(slateRelationship.QUANTITY_SUM, slateRelationship.CONTRAST, False)
                    rel.calc_cumulative_relationship(slateRelationship.QUANTITY_SUM, slateRelationship.PCT_RATIO, False)
                    
                    print('----- Reverse -----')
                    rel.calc_cumulative_relationship(slateRelationship.NUM_SIGNATURES, slateRelationship.CONTRAST, True)
                    rel.calc_cumulative_relationship(slateRelationship.NUM_SIGNATURES, slateRelationship.PCT_RATIO, True)
                    rel.calc_cumulative_relationship(slateRelationship.QUANTITY_SUM, slateRelationship.CONTRAST, True)
                    rel.calc_cumulative_relationship(slateRelationship.QUANTITY_SUM, slateRelationship.PCT_RATIO, True)

                    # rel.calc_cumulative_relationship(slateRelationship.QUANTITY_SUM, slateRelationship.CONTRAST, False)
            else:
                algaeApp.error_message('Histogram not found for layer ' + layer.geoprocess.name + '.')
        
    def loop_through_layers(self, place_set):
    #------------------------------------------------------------------------------
        """
        """
        l = slateLayer()
        rowids_array = l.get_layers_for_project(self.db, place_set.project.rowid, self.resolution.rowid)
        if rowids_array != None and len(rowids_array) > 0:
            num_checked = 0
            for rowid in rowids_array:
                self.process_layer(rowid, place_set)
                num_checked += 1
                if self.args.max_to_run != None and num_checked >= self.args.max_to_run:
                    break
                            
    def run(self):
    #------------------------------------------------------------------------------
        """
        """
        self.debug = self.args.debug
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
            if self.debug: print(ps.sql)
            if self.args.layer_rowid_fk == None:
                self.loop_through_layers(ps)
            else:
                self.process_layer(self.args.layer_rowid_fk, ps)
        else:
            algaeApp.error_message('Problem reading place set.')
        
        
        
        