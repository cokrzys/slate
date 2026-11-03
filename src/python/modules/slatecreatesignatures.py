"""

 Slate | Create signatures.
 
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

from slateapp import slateApp
from refresolution import refResolution
from slateplace import slatePlace
from slatestudyarea import slateStudyArea
from slatelayer import slateLayer
from slatesignature import slateSignature

class slateCreateSignatures:
    
    def __init__(self, db, args):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        self.db = db
        self.args = args
        self.place = slatePlace()
        self.study_area = slateStudyArea()
        self.mask_layer = slateLayer()
        self.model_layer = slateLayer()
        self.trans_pt = None
        self.cell_coords = None
        self.num_model_layers = 0
        
    def get_colored_filename(self, source_filename):
    #------------------------------------------------------------------------------
        """
        """
        filename = os.path.basename(source_filename)
        filename_parts = os.path.splitext(filename)
        suffix = app.config.colored_suffix + '.png'
        return os.path.join(os.path.dirname(source_filename), filename_parts[0] + suffix)
    
    def get_temp_filename(self, source_filename, suffix, extension):
    #------------------------------------------------------------------------------
        filename = os.path.basename(source_filename)
        filename_parts = os.path.splitext(filename)
        return os.path.join(os.path.dirname(source_filename), filename_parts[0] + suffix + extension)
    
    def get_annotated_filename(self, source_filename):
    #------------------------------------------------------------------------------
        filename = os.path.basename(source_filename)
        filename_parts = os.path.splitext(filename)
        suffix = app.config.annotated_suffix + '.png'
        return os.path.join(os.path.dirname(source_filename), filename_parts[0] + suffix)
    
    def get_overlay_filename(self, db, project_rowid_fk, resolution_rowid_fk):
    #------------------------------------------------------------------------------
        l = slateLayer()
                        
        sql = u"""SELECT l.rowid
                    FROM sp.layer l
                    INNER JOIN sp.geoprocess gp ON l.geoprocess_rowid_fk = gp.rowid
                    WHERE gp.project_rowid_fk = %(project_rowid_fk)s
                        AND gp.data_type_rowid_fk = (SELECT rowid FROM ref.data_type WHERE name = 'Overlay')
                        AND l.resolution_rowid_fk = %(resolution_rowid_fk)s"""
                        
        parms = {'project_rowid_fk': project_rowid_fk, 'resolution_rowid_fk': resolution_rowid_fk}
        l.rowid = db.get_scalar_integer(sql, parms)
        if l.rowid != None:
            l.read_row_from_database_with_rowid(db, l.rowid, True)
            print(l.filename)
            overlay_filename = l.get_fully_pathed_overlay_filename()
            if overlay_filename != None:
                if os.path.exists(overlay_filename):
                    return overlay_filename
                else:
                    algaeApp.error_message('Overlay file ' + overlay_filename + ' does not exist.')
            else:
                algaeApp.error_message('Unable to parse and build the overlay filename for ' + l.filename + '.')
        return None
            
    def make_colored_raster(self, source_filename, palette_file):
    #------------------------------------------------------------------------------
        """
        
        gdaldem color-relief \
        -alpha \
        /dat/slate/bsed/pl/000/003/sim_w_wc_bio_13_100000.tiff \
        qgis_spectral_5_color.txt \
        bio13_test_colored.tiff
        
        """
        print('Coloring %s with %s.' % (source_filename, palette_file))
        colored_filename = self.get_colored_filename(source_filename)
        subprocess.run(["gdaldem", "color-relief", "-alpha", source_filename, palette_file, colored_filename]) 
    
    def make_annotated_raster(self, source_filename, layer, resolution, northwest_text = None, northeast_text = None):
    #------------------------------------------------------------------------------
        """
        convert \
        bio13_test_colored.tiff \
        -gravity SouthWest \
        -pointsize 12 \
        -fill black \
        -font "Arial" \
        -annotate +15+15 \
        "nSignature for Denver\nBIO13 Precipitation of Wettest Month (mm)" \
        bio13_test_colored_and_annotated.tiff
        """
                
        now_str = datetime.datetime.today().strftime("%d-%b-%Y %H:%M:%S UTC")
        
        colored_filename = self.get_colored_filename(source_filename)
        annotated_filename = self.get_annotated_filename(source_filename)
        overlay_filename = self.get_overlay_filename(self.db, self.place.project.rowid, resolution.rowid)
        temp_filename = self.get_temp_filename(source_filename, '_t1', '.png')
        
        if overlay_filename != None:
            command = ['composite']
            command.append(overlay_filename)
            command.append(colored_filename)
            command.append(temp_filename)
            subprocess.run(command)
        
        command = ['convert']
        if os.path.exists(temp_filename):
            command.append(temp_filename)
        else:
            command.append(colored_filename)
            
        #
        # ----- upper area
        #
        
        aa_ysize = 37
        str_xmax = str(self.mask_layer.xsize - 1)
        str_ymax = str(self.mask_layer.ysize - 1)
        
        command.append('-stroke')
        command.append('none')
        command.append('-fill')
        command.append('#FFFFFFCC')
        command.append('-draw')
        command.append('rectangle 0,0 ' + str_xmax + ',' + str(aa_ysize))
        
        command.append('-gravity')
        command.append('NorthWest')
        command.append('-fill')
        command.append('black')
        command.append('-pointsize')
        command.append('12')
        command.append('-annotate')
        command.append('+10+14')
        
        text = self.place.project.name + ' | ' + layer.geoprocess.name
        if northwest_text != None: text = northwest_text
        
        command.append(text)
        
        command.append('-gravity')
        command.append('NorthEast')
        command.append('-fill')
        command.append('black')
        command.append('-pointsize')
        command.append('12')
        command.append('-annotate')
        command.append('+10+14')
        
        text = 'Signature for ' + self.place.name
        if northeast_text != None: text = northeast_text
        
        command.append(text)
        
#        presentation version
#         command.append('-gravity')
#         command.append('North')
#         command.append('-pointsize')
#         command.append('20')
#         command.append('-fill')
#         command.append('black')
#         command.append('-annotate')
#         command.append('+15+15')
#         command.append(self.place.project.name + ' ' + layer.geoprocess.name + '\nSignature for ' + self.place.name)
        
        #
        # ----- lower area
        #
        
        command.append('-stroke')
        command.append('none')
        command.append('-fill')
        command.append('#FFFFFFCC')
        command.append('-draw')
        command.append('rectangle 0,' + str(self.mask_layer.ysize - aa_ysize) + ' ' + str_xmax + ',' + str_ymax)
        
        command.append('-gravity')
        command.append('SouthWest')
        command.append('-fill')
        command.append('black')
        command.append('-pointsize')
        command.append('12')
        command.append('-annotate')
        command.append('+10+11')
        command.append(os.path.basename(annotated_filename) + ' | ' + now_str)
        
        command.append('-gravity')
        command.append('SouthEast')
        command.append('-fill')
        command.append('black')
        command.append('-pointsize')
        command.append('12')
        command.append('-annotate')
        command.append('+10+11')
        command.append('Copyright © 2026' + self.place.project.copyright)
        
        command.append('-stroke')
        command.append('black')
        command.append('-fill')
        command.append('yellow')
        command.append('-strokewidth')
        command.append('1')
        center = str(self.cell_coords[0]) + ',' + str(self.cell_coords[1])
        command.append('-draw')
        command.append('translate ' + center + ' circle 0,0 6,0')
        
        command.append(annotated_filename)    
        subprocess.run(command)
        
        if os.path.exists(temp_filename):
            os.remove(temp_filename)
            
    def add_to_model(self, signature_layer, layer, resolution):
    #------------------------------------------------------------------------------
        """
        """
        if self.model_layer.data is None:
            self.model_layer.create_zero_raster_from_layer(self.mask_layer, np.float32)
        #
        #
        #
        # if len(self.model_layer.data) > 0:
        if self.model_layer.data is not None:
            print('Adding layer ' + signature_layer.filename + ' to the model.')
            self.model_layer.data = self.model_layer.data + signature_layer.data.astype(np.float32)
            self.num_model_layers += 1
            
            incremental_model = slateLayer()
            incremental_model.create_zero_raster_from_layer(self.mask_layer, np.float32)
            incremental_model.data = self.model_layer.data / self.num_model_layers
            
            output_file = self.place.get_directory(resolution.folder) + self.place.abbreviation
            output_file += '_sig_model_' + str(self.num_model_layers).zfill(3) + '_layers.tiff'
            print('DEBUG: ' + output_file)
            
            incremental_model.data[self.mask_layer.data == self.mask_layer.nodata_value] = slateApp.NODATA_FLOAT32
            incremental_model.write_raster(output_file, 6, slateApp.NODATA_FLOAT32)
            
            self.make_colored_raster(output_file, app.config.model_palette_file)
            
            northwest_text = self.place.project.name + ' | ' + self.place.name + ' Signature Model'
            northeast_text = str(self.num_model_layers).zfill(3) + ' Layer(s)'
            
            self.make_annotated_raster(output_file, layer, resolution, northwest_text, northeast_text)
            
        
    def create_signature_raster(self, signature, layer, resolution):
    #------------------------------------------------------------------------------
        """
        """
        self.place.create_directory(resolution.folder)
        #
        #
        #
        signature_layer = slateLayer()
        if signature_layer.create_zero_raster_from_layer(self.mask_layer, np.float32):
            
            if layer.geoprocess.data_type.name == 'Categorical':
                signature_layer.data[layer.data == signature.numeric_val] = 1
            else:
                #
                #
                #
                data_range = None
                if layer.sig_lower_cutoff != None and layer.sig_upper_cutoff != None:
                    data_range = float(layer.sig_upper_cutoff - layer.sig_lower_cutoff)
                else:
                    data_range = float(layer.data_q3 - layer.data_q1)
                    
                print('data_range = %r' % data_range)
                #
                # similarity = 1 - (abs(signature - val) / range) when abs(signature - val) < range
                # similarity = 0 when abs(signature - val) >= range
                #
                signature_layer.data = (1 - (abs(signature.numeric_val - layer.data) / data_range)).astype(np.float32)
                signature_layer.data[abs(signature.numeric_val - layer.data) >= data_range] = 0
                
                offset = 10
                
                val_p2 = layer.get_data_value(self.cell_coords[0] + offset, self.cell_coords[1] + offset)
                print('Value at (%r, %r) = %r' % (self.cell_coords[0] + offset, self.cell_coords[1] + offset, val_p2))
                
                test_val = signature_layer.get_data_value(self.cell_coords[0], self.cell_coords[1])
                print('Test val = %r, it should be 1.' % (test_val))
                
                second_val = 1 - (abs(signature.numeric_val - val_p2) / data_range)
                
                test_val = signature_layer.get_data_value(self.cell_coords[0] + offset, self.cell_coords[1] + offset)
                print('Second test val = %r, it should not be %r.' % (test_val, second_val))
                
            #
            # ----- apply the mask layer
            #
            signature_layer.data[self.mask_layer.data == self.mask_layer.nodata_value] = slateApp.NODATA_FLOAT32
            
            print(layer.filename)
            
            #
            #
            #
            filename_parts = os.path.splitext(os.path.basename(layer.filename))
            output_file = self.place.get_directory(resolution.folder) + filename_parts[0]
            output_file += '_' + signature.place.abbreviation + '_sig' + filename_parts[1]
            
            print(output_file)
            
            signature_layer.write_raster(output_file, 6, slateApp.NODATA_FLOAT32) # 6 = gdal float32 code
            signature_layer.filename = output_file
            
            self.make_colored_raster(output_file, app.config.model_palette_file)
            
            self.make_annotated_raster(output_file, layer, resolution)
            
            #
            #
            #
            if self.args.build_model:
                self.add_to_model(signature_layer, layer, resolution)
        
    def create_signature(self, layer, resolution):
    #------------------------------------------------------------------------------
        """
        """
        val = layer.get_data_value(self.cell_coords[0], self.cell_coords[1])
        if val != None:
            if layer.is_nodata(val):
                print('Signature for %s is NoData.' % (layer.get_fully_pathed_filename()))
            else:
                print('Signature for %s is %r.' % (layer.get_fully_pathed_filename(), round(val, layer.geoprocess.num_decimals)))
            #
            #
            #
            sig = slateSignature()
            # sig.place.rowid = self.place.rowid
            sig.place = self.place # TODO: Does this copy everything SEEMS NOT
            sig.place.rowid = self.place.rowid
            sig.layer.rowid = layer.rowid
            sig.numeric_val = float(val)
            sig.write_to_database(self.db)
            #
            #
            #
            self.create_signature_raster(sig, layer, resolution)
            
        
    def loop_through_layers(self, resolution):
    #------------------------------------------------------------------------------
        """
        """
        rowids_array = self.mask_layer.get_layers_for_project(self.db, self.place.project.rowid, resolution.rowid)
        if rowids_array != None and len(rowids_array) > 0:
            num_used = 0
            for rowid in rowids_array:
                l = slateLayer()
                l.read_row_from_database_with_rowid(self.db, rowid, True)
                #
                # ----- exclude overlay layers
                #       TODO: Make a less hard-coded way to do this.
                #
                if l.geoprocess.data_type.name != 'Overlay':
                    # print('Creating signatures for layer %s.' % l.filename)
                    l.read_data(True) # metadata only
                    if l.ok_to_use(self.study_area, self.mask_layer, l):
                        l.read_data() # all data
                        self.create_signature(l, resolution)
                        num_used += 1
                    if num_used >= self.args.testing_max_layers:
                        break
                    
    def create(self):
    #------------------------------------------------------------------------------
        """
        """
        self.place.read_row_from_database_with_rowid(self.db, self.args.place_rowid_fk, True)
        if self.place.project.rowid != None:
            print('Creating signature for ' + self.place.name + ' using the ' + self.place.project.name + ' dataset.')
            self.study_area.read_row_from_database_with_project_rowid(self.db, self.place.project.rowid, True)
            if self.study_area.srid_fk != None:
                print('Project coordinate system = EPSG:' + str(self.study_area.srid_fk))
                print('Study area GeoProcess rowid = ' + str(self.study_area.geoprocess.rowid))
                resolution = refResolution()
                resolution.read_row_from_database_with_rowid(self.db, self.args.resolution_rowid_fk)
                if resolution.folder != None:  # TODO: Better way to determine if read ok.
                    print('Resolution (cell size x) is ' + str(resolution.cell_size_x))
                    #
                    # ----- transform place location to project coordinate system
                    #
                    print(self.place.name + " XY (EPSG:4326) = %r, %r" % (self.place.longitude, self.place.latitude))
                    self.trans_pt = slateApp.transform_pt(self.db, self.place.longitude, self.place.latitude, 4326, self.study_area.srid_fk)
                    if self.trans_pt != None and len(self.trans_pt) == 2:
                        print(self.place.name + " XY (EPSG:" + str(self.study_area.srid_fk) + " = %r, %r" % (self.trans_pt))
                        #
                        # ----- mask layer
                        #
                        self.mask_layer.read_row_from_database_with_geoprocess_and_resolution(self.db, self.study_area.geoprocess.rowid, 
                                                                                              resolution.rowid, True)
                        if self.mask_layer.rowid != None and self.mask_layer.rowid > 0:
                            if self.mask_layer.read_data():
                                if self.mask_layer.ok_to_use(self.study_area, self.mask_layer, self.mask_layer):
                                    #
                                    # ----- get raster coordinates for world coordinates
                                    #
                                    self.cell_coords = self.mask_layer.get_cell_coords(self.trans_pt[0], self.trans_pt[1])
                                    print('Cell coordinates (ix, iy) = (%r, %r)' % self.cell_coords)
                                    #
                                    # ----- make sure place is within the study area
                                    #
                                    val = self.mask_layer.get_data_value(self.cell_coords[0], self.cell_coords[1])
                                    if val != None:
                                        if not self.mask_layer.is_nodata(val):
                                            #
                                            # ----- loop through all the layers and create signatures
                                            #
                                            self.loop_through_layers(resolution)
                                        else:
                                            algaeApp.error_message(self.place.name + ' is not in the study area.')
                                    else:
                                        algaeApp.error_message('Unable to get a data value from the mask.')
                        else:
                            algaeApp.error_message('Unable to load the mask layer for geoprocess ' + self.study_area.geoprocess.name +
                                                   ' and resolution ' + str(resolution.cell_size_x) + '.')
        
        
        