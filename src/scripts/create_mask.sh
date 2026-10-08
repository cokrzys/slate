#!/bin/bash
#
#  slate | Create a mask raster.
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

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'Creating Mask'
echo "Source = "$SOURCE
echo "Output filename = "$DEST

#
# ----- update layer calc begin timestamp
#
"$SLATE_PYTHON_APPS_FOLDER"updatelayerfiledates.py \
--layer_rowid_fk $(jq -r '.layerRowid' $PARMS) \
--column begin

#
# ----- convert vector to raster
# 
echo -e "\nConverting shapefile to raster."
if [ $(jq '.invert' $PARMS) == '"True"' ]; then
  gdal_rasterize \
  -burn 1 \
  -i \
  -ot Byte \
  -a_nodata $NODATA_BYTE \
  -init $NODATA_BYTE \
  -a_srs 'EPSG:'$SRID \
  -te $XMIN $YMIN $XMAX $YMAX \
  -tr $RESOLUTION $RESOLUTION \
  -l $SOURCE_BASE \
  "$SOURCE" \
  $WORKING_FOLDER"_t1.tiff"
else
  gdal_rasterize \
  -burn 1 \
  -ot Byte \
  -a_nodata $NODATA_BYTE \
  -init $NODATA_BYTE \
  -a_srs 'EPSG:'$SRID \
  -te $XMIN $YMIN $XMAX $YMAX \
  -tr $RESOLUTION $RESOLUTION \
  -l $SOURCE_BASE \
  "$SOURCE" \
  $WORKING_FOLDER"_t1.tiff"
fi

#
# ----- make the legend
#
echo -e "\nMaking the legend."
"$SLATE_PYTHON_APPS_FOLDER"createmasklegend.py \
$WORKING_FOLDER"mask_legend.json"

#
# ----- color and make classes
#
echo -e "\nColoring and making classes."
"$SLATE_PYTHON_APPS_FOLDER"color_and_make_classes.py \
-lrowid $(jq -r '.layerRowid' $PARMS) \
-a_nodata $NODATA_BYTE \
$WORKING_FOLDER"_t1.tiff" \
"$DEST" \
$WORKING_FOLDER"mask_legend.json"

#
# ----- make thumbnails
#
if [ -f "$DEST" ];
then
  "$SLATE_SCRIPTS_FOLDER"make_thumbnails.sh $PARMS "$DEST" "$DEST"
fi

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


 
