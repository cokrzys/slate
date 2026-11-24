#!/bin/bash
#
#  slate | Categorical from a shapefile GeoProcess.
#
#  @author    Brian Krzys (brian.krzys@rtspatial.com)
#  @copyright (c) 2026 RTSpatial Ltd.
#  @license   SPDX-License-Identifier: MIT
#  @link      https://github.com/cokrzys/slate
#

#
# ----- JSON parameters file
#
PARMS=$1

#
# ----- setup common environment variables
#       note . $SETVARS makes them accessible to this environment
#
SETVARS=$(jq -r '.scriptsFolder' $PARMS)'set_common_vars.sh'
. $SETVARS $PARMS

#
# ----- variables unique to this script
#
SOURCE=$(jq -r '.shapefile' $PARMS)
SOURCE_BASE=`basename $SOURCE .shp`
DEST_FILENAME=$(jq -r '.layerFilename' $PARMS)
DEST=$WORKING_FOLDER$DEST_FILENAME
DEST_BASE=`basename $DEST .tiff`
CODE_FIELD=$(jq -r '.attribute_field' $PARMS)

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'Categorical Raster from a Shapefile'
echo "Input filename = "$SOURCE
echo "Output filename = "$DEST

#
# ----- project to study area coordinate system
#
echo -e "\nRe-projecting."
  ogr2ogr \
  -t_srs 'EPSG:'$SRID \
  -overwrite \
  -f 'ESRI Shapefile' $WORKING_FOLDER"_t0.shp" \
  "$SOURCE"
  
#
# ----- setup optional legend_rowid_fk parameter
#
ARRAY_OPTIONAL_PARAMETERS=()
if [ "$(jq -r '.legend_rowid_fk' $PARMS)" ] && [ "$(jq -r '.legend_rowid_fk' $PARMS)" != '' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( --legend_rowid_fk )
  ARRAY_OPTIONAL_PARAMETERS+=( "$(jq -r '.legend_rowid_fk' $PARMS)" )
fi

#
# ----- code categorical values
#
echo -e "\nCreating numeric codes for categorical values."
  "$SLATE_PYTHON_APPS_FOLDER"addcatcodes.py \
  -af $(jq -r '.attribute_field' $PARMS) \
  -ot $OUTPUT_DATA_TYPE \
  "${ARRAY_OPTIONAL_PARAMETERS[@]}" \
  $WORKING_FOLDER"_t0.shp" \
  $WORKING_FOLDER"_t1.shp"

#
# ----- convert vector to raster
# 
echo -e "\nConverting shapefile to raster."
  gdal_rasterize \
  -a 'slate_code' \
  -ot $OUTPUT_DATA_TYPE \
  -a_nodata ${OUTPUT_NODATA%.*} \
  -init ${OUTPUT_NODATA%.*} \
  -a_srs 'EPSG:'$SRID \
  -te $XMIN $YMIN $XMAX $YMAX \
  -tr $RESOLUTION $RESOLUTION \
  -l '_t1' \
  $WORKING_FOLDER"_t1.shp" \
  $WORKING_FOLDER"_t2.tiff"

#
# ----- apply study area mask
#
echo -e "\nApplying study area mask."
  "$SLATE_PYTHON_APPS_FOLDER"mask.py \
  $(jq -r '.maskFilename' $PARMS) \
  $WORKING_FOLDER"_t2.tiff" \
  $WORKING_FOLDER"_t3.tiff" \
  -mv $NODATA_BYTE \
  -ov ${OUTPUT_NODATA%.*} \
  -nodata ${OUTPUT_NODATA%.*}
  
#
# ----- setup optional legend_rowid_fk parameter
#
ARRAY_OPTIONAL_PARAMETERS=()
if [ "$(jq -r '.legend_rowid_fk' $PARMS)" ] && [ "$(jq -r '.legend_rowid_fk' $PARMS)" != '' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( --legend_rowid_fk )
  ARRAY_OPTIONAL_PARAMETERS+=( "$(jq -r '.legend_rowid_fk' $PARMS)" )
fi

#
# ----- color and make classes
#
echo -e "\nCreating classes and making a colored raster."
  "$SLATE_PYTHON_APPS_FOLDER"colorwithlegend.py \
  -lrowid $(jq -r '.layerRowid' $PARMS) \
  "${ARRAY_OPTIONAL_PARAMETERS[@]}" \
  $WORKING_FOLDER"_t3.tiff" \
  "$DEST"

#
# ----- make thumbnails
#
if [ -f "$DEST" ]
then
  "$SLATE_SCRIPTS_FOLDER"make_thumbnails.sh $PARMS "$DEST" "$DEST"
fi

#
# ----- delete temporary files
#
"$SLATE_SCRIPTS_FOLDER"delete_temporary_files.sh $WORKING_FOLDER

#
# ----- update layer calc end timestamp
#
"$SLATE_PYTHON_APPS_FOLDER"updatelayerfiledates.py \
  --layer_rowid_fk $(jq -r '.layerRowid' $PARMS) \
  --column end


#
# ----- show environment
#
"$SLATE_SCRIPTS_FOLDER"show_environment.sh $PARMS