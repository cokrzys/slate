"""

 slate | Run one or more geoprocesses.
 
 Supports the rungeoprocesses application.

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
import json

import numpy as np

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblcoreprocess import algaeTblCoreProcess

from refresolution import refResolution
from slateapp import slateApp
from slategeoprocess import slateGeoProcess
from slategeoprocimportraster import slateGeoProcImportRaster
from slatestudyarea import slateStudyArea
from slatelayer import slateLayer

class slateRunGeoprocesses:
    
    def __init__(self, db, args):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.db = db
        self.args = args
        self.core_process = None # an instance of algaeTblCoreProcess if it's used
        self.resolution = refResolution()
        
    def run_one(self, rowid):
    #------------------------------------------------------------------------------
        """
        """
        gp = slateGeoProcess()
        study_area = slateStudyArea()
        mask_layer = slateLayer()
        #
        #
        #
        gp.read_row_from_database_with_rowid(self.db, rowid, True)
        if gp.rowid != None:
            #
            # ----- messaging
            #
            msg = 'Running ' + gp.name + '.'
            print(msg)
            if self.core_process != None:
                self.core_process.update_message(self.db, msg)
            
            if gp.php_class == 'slateGeoProcReProjectRaster':
                gp = slateGeoProcImportRaster()
                gp.read_row_from_database_with_rowid(self.db, rowid)
        
            #
            # ----- layers that don't require a mask
            #
            if gp.php_class == 'slateGeoProcMask':
                gp.require_mask_layer = False
                
            if gp.create_directory(self.resolution.folder):
                #
                # ----- read study area if we don't already have it
                #
                if study_area.rowid == None:
                    study_area.read_row_from_database_with_project_rowid(self.db, gp.project.rowid, True)
                #
                # ----- read mask layer if we don't already have it
                #
                if study_area.rowid != None and mask_layer.rowid == None:
                    mask_layer.read_row_from_database_with_geoprocess_and_resolution(self.db, study_area.geoprocess.rowid, 
                                                                                              self.resolution.rowid, True)
                #
                #
                #
                if study_area.rowid != None:
                    
                    if (gp.require_mask_layer and mask_layer.file_exists()) or not gp.require_mask_layer:
                        
                        parms = {}
                        parms['scriptsFolder'] = os.path.join(app.config.scripts_folder, '')
                        parms['pythonAppsFolder'] = os.path.join(app.config.python_apps_folder, '')
                        parms['palettesFolder'] = os.path.join(app.config.palettes_folder, '')
                        parms['workingFolder'] = os.path.join(gp.get_directory(self.resolution.folder), '')
                        parms['outputDataType'] = gp.data_type.name
                        parms['outputNoDataValue'] = gp.data_type.nodata_value
                        parms['noDataByte'] = app.config.nodata_byte
                        parms['noDataFloat32'] = app.config.nodata_float32
                        parms['resolutionName'] = self.resolution.name
                        parms['resolutionFolder'] = self.resolution.folder
                        parms['resolution'] = self.resolution.cell_size_x
                        study_area.set_thumbnail_size(self.resolution.cell_size_x, self.resolution.cell_size_y)
                        # sends too much redundant information
                        # parms['studyArea'] = json.loads(json.dumps(study_area, default=lambda x: x.__dict__))
                        parms['srid_fk'] = study_area.srid_fk
                        parms['min_x'] = study_area.min_x
                        parms['max_x'] = study_area.max_x
                        parms['min_y'] = study_area.min_y
                        parms['max_y'] = study_area.max_y
                        parms['minLat'] = study_area.min_x
                        parms['maxLat'] = study_area.max_x
                        parms['minLong'] = study_area.min_y
                        parms['maxLong'] = study_area.max_y
                        parms['thumb_x'] = study_area.thumb_x
                        parms['thumb_y'] = study_area.thumb_y
                        parms['projectRowid'] = gp.project.rowid
                        parms['projectName'] = gp.project.name
                        parms['projectAbbreviation'] = gp.project.abbreviation
                        parms['userRowid'] = gp.project.app_user.rowid
                        #
                        #
                        #
                        study_area.read_lat_long_bounds(self.db)
                        parms['minLat'] = study_area.lat_long_bounds_with_buffer.lat_min
                        parms['maxLat'] = study_area.lat_long_bounds_with_buffer.lat_max
                        parms['minLong'] = study_area.lat_long_bounds_with_buffer.long_min
                        parms['maxLong'] = study_area.lat_long_bounds_with_buffer.long_max
                        #
                        # ----- mask layer will not exist when creating the mask layer itself
                        #
                        if mask_layer.rowid != None:
                            # setattr(mask_layer, 'fullFilename', mask_layer.get_fully_pathed_filename())
                            parms['maskFilename'] = mask_layer.get_fully_pathed_filename()
                            # parms['maskLayer'] = json.loads(json.dumps(mask_layer, default=lambda x: x.__dict__))
                        #
                        #
                        #
                        gp.parameters_to_variables(self.db, parms)
                        #
                        #
                        #
                        output_prefix_attribute_name = 'output_prefix'
                        layer = slateLayer()
                        layer.geoprocess.rowid = gp.rowid
                        layer.resolution.name = self.resolution.name
                        # layer.debug = True
                        if layer.exists(self.db):
                            layer.read_row_from_database_with_geoprocess_and_resolution(self.db, layer.geoprocess.rowid, 
                                                                                        self.resolution.rowid, True)
                            if output_prefix_attribute_name in parms:
                                old_filename = layer.filename
                                layer.make_filename(parms[output_prefix_attribute_name], self.resolution.folder)
                                if layer.filename != old_filename:
                                    layer.update(self.db)
                        else:
                            # print('DEBUG: Layer does not exist, creating it.')
                            if output_prefix_attribute_name in parms:
                                layer.make_filename(parms[output_prefix_attribute_name], self.resolution.folder)
                                if layer.insert(self.db):
                                    print('Layer ' + layer.filename + ' added to the database.')
                                else:
                                    algaeApp.error_message('Unable to add the layer to the database.')
                            else:
                                algaeApp.error_message(output_prefix_attribute_name + ' not found in parms dictionary.')
                                    
                        parms['layerFilename'] = layer.filename
                        parms['layerRowid'] = layer.rowid
                        # too much information
                        # parms['layer'] = json.loads(json.dumps(layer, default=lambda x: x.__dict__))
                        
                        # gpj = json.loads(json.dumps(gp, default=lambda x: x.__dict__))
                        
    #                     if 'parameters' in gpj and gpj['parameters'] != None:
    #                         gpj['parameters'] = json.loads(gpj['parameters'])
    #                     if 'batch_parameters' in gpj and gpj['batch_parameters'] != None:
    #                         gpj['batch_parameters'] = json.loads(gpj['batch_parameters'])
                        # parms['geoProcess'] = gpj
                        
                        # print(json.dumps(parms, indent=2))
                        
                        parms_filename = os.path.join(gp.get_directory(self.resolution.folder), 'parameters.json')
                        
                        with open(parms_filename, 'w', encoding='utf-8') as f:
                            json.dump(parms, f, ensure_ascii=False, indent=2)
                        
                        command = os.path.join(app.config.scripts_folder, gp.command)
                            
                        process = subprocess.Popen([command, parms_filename], text=True, stdout=subprocess.PIPE)
                        
                        # print("--- Live Streamed Output ---")
                        # Read output line-by-line until the process is done and the stream is closed
                        for line in process.stdout:
                            # sys.stdout.write(f"[Child Output] {line}")
                            sys.stdout.write(line)
                            sys.stdout.flush() # Ensure the output is immediately shown
                        
                        process.wait() # Wait for the process to truly finish
                    
                        # print(process.stdout) this is already cleared, nothing to print
                    else:
                        algaeApp.error_message('Mask does not exist.')
                    
                else:
                    algaeApp.error_message('Unable to load the study area for project rowid ' + str(gp.project.rowid) + '.')
            else:
                algaeApp.error_message('Unable setup directory ' + gp.get_directory(self.resolution.folder))
        else:
            algaeApp.error_message('Unable to load geoprocess for rowid ' + str(rowid) + '.')
                    
    def run(self):
    #------------------------------------------------------------------------------
        """
        """
        gp = slateGeoProcess()
        ok = True
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
        # ----- setup sql to read single item or multiple
        #
        sql = "SELECT rowid FROM " + gp.table_name + " ";
        parms = {}
        if self.args.geoprocess_rowid_fk != None:
            #
            # ----- run a single geoprocess
            #
            sql += "WHERE rowid = %(geoprocess_rowid_fk)s"
            parms = {'geoprocess_rowid_fk': self.args.geoprocess_rowid_fk}
        elif self.args.where != None:
            #
            # ----- run with a where clause
            #       project_rowid_fk is also always required here
            #
            sql += "WHERE project_rowid_fk = %(project_rowid_fk)s AND "
            sql += self.args.where
            sql += " AND sequence < 1000 ORDER BY sequence, name ASC"
            parms = {'project_rowid_fk': self.args.project_rowid_fk}
        elif self.args.project_rowid_fk != None:
            #
            # ----- run everything for a project
            #
            sql += "WHERE project_rowid_fk = %(project_rowid_fk)s AND sequence < 1000 ORDER BY sequence, name ASC"
            parms = {'project_rowid_fk': self.args.project_rowid_fk}
        else:
            algaeApp.error_message('Run criteria not specified.')
            ok = False
        #
        # ----- connect to the core process log
        #
        if self.args.process_rowid_fk != None:
            self.core_process = algaeTblCoreProcess()
            self.core_process.read_row_from_database_with_rowid(self.db, self.args.process_rowid_fk)
            if self.core_process.rowid != None and self.core_process.rowid > 0:
                print('Process logging with command ' + self.core_process.command)
            else:
                self.core_process = None  # remove instance so it's not used
        #
        # ----- loop through one or multiple items
        #
        if ok:
            if self.args.max_to_run != None:
                sql += ' LIMIT ' + str(self.args.max_to_run)
            data = self.db.get_all(sql, parms)
            if data != None:
                print('%r geoprocess(es) read.' % len(data))
                num_finished = 0
                for row in data:
                    self.run_one(int(row[0]))
                    num_finished += 1
                    pct_finished = float(num_finished) / float(len(data)) * 100.0
                    if self.core_process != None:
                        self.core_process.update_progress(self.db, pct_finished)
                        if pct_finished == 100.0:
                            msg = 'Finished running ' + str(len(data)) + ' geoprocess(es).'
                            self.core_process.update_message(self.db, msg)
                    
        
            
        
        
        
        
        