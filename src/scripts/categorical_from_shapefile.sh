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

echo $WORKING_FOLDER"_t1.shp"

#
# ----- code categorical values
#
echo -e "\nCreating numeric codes for categorical values."
"$SLATE_PYTHON_APPS_FOLDER"addcatcodes.py \
-af $(jq -r '.attribute_field' $PARMS) \
-ot Byte \
"$SOURCE" \
$WORKING_FOLDER"_t1.shp"

exit

#
# ----- convert vector to raster
# 
echo -e "\nConverting shapefile to raster."
gdal_rasterize \
-a 'slate_code' \
-ot $OUTPUT_TYPE \
-a_nodata $(jq '.geoProcess.outputType.nodataValue' $PARMS) \
-init $(jq '.geoProcess.outputType.nodataValue' $PARMS) \
-a_srs 'EPSG:'$SRID \
-te $XMIN $YMIN $XMAX $YMAX \
-tr $RESOLUTION $RESOLUTION \
-l '_t1' \
$FOLDER"_t1.shp" \
$FOLDER"_t2.tiff"

#
# ----- apply study area mask
#
echo -e "\nApplying study area mask."
"$SLATE_PYTHON_APPS_FOLDER"mask.py \
$(jq -r '.studyArea.maskFilename' $PARMS) \
$FOLDER"_t2.tiff" \
$FOLDER"_t3.tiff" \
-ov $(jq '.geoProcess.outputType.nodataValue' $PARMS) \
-nodata $(jq '.geoProcess.outputType.nodataValue' $PARMS)

#
# ----- color and make classes
#
echo -e "\nColoring final raster and writing classes to the database."
"$SLATE_PYTHON_APPS_FOLDER"color_and_make_classes.py \
-lrowid $(jq -r '.categoricalParameters.layer.rowid' $PARMS) \
-a_nodata $(jq '.geoProcess.outputType.nodataValue' $PARMS) \
$FOLDER"_t3.tiff" \
"$DEST" \
$FOLDER"$LEGEND_FILENAME"

#
# ----- make thumbnails
#
if [ -f "$DEST" ]
then

  "$SLATE_SCRIPTS"/make_thumbnails.sh $PARMS "$DEST"
  
fi

#
# ----- show environment
#
"$SLATE_SCRIPTS"/show_environment.sh