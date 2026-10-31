#!/bin/bash
#
#  slate | Import a raster.
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
SOURCE=$(jq -r '.rasterFilename' $PARMS)
DEST_FILENAME=$(jq -r '.layerFilename' $PARMS)
DEST=$WORKING_FOLDER$DEST_FILENAME
DEST_BASE=`basename $DEST .tiff`
DEST_COLORED=$WORKING_FOLDER$DEST_BASE"_colored.png"

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'Import Raster'
echo "Input filename = "$SOURCE
echo "Output filename = "$DEST

#
# ----- update layer calc begin timestamp
#
"$SLATE_PYTHON_APPS_FOLDER"updatelayerfiledates.py \
--layer_rowid_fk $(jq -r '.layerRowid' $PARMS) \
--column begin

# rm $WORKING_FOLDER*

#
#
#
OUTPUT_NODATA=$(jq -r '.outputNoDataValue' $PARMS)
echo 'Output type is '$(jq -r '.outputDataType' $PARMS)' with a NoData value = '$OUTPUT_NODATA

#
# ----- re-project raster
# 
echo -e "\nRe-projecting."
gdalwarp \
-t_srs 'EPSG:'$SRID \
-te $XMIN $YMIN $XMAX $YMAX \
-tr $RESOLUTION $RESOLUTION \
-ot $(jq -r '.outputDataType' $PARMS) \
-dstnodata $OUTPUT_NODATA \
-overwrite \
-r $(jq -r '.resampling_method' $PARMS) \
"$SOURCE" \
$WORKING_FOLDER"_t1.tiff"

echo 'Mask filename = '$(jq -r '.maskFilename' $PARMS)

#
# ----- apply study area mask
#
echo -e "\nApplying study area mask."
"$SLATE_PYTHON_APPS_FOLDER"mask.py \
$(jq -r '.maskFilename' $PARMS) \
$WORKING_FOLDER"_t1.tiff" \
$WORKING_FOLDER"_t2.tiff" \
-mv $NODATA_BYTE \
-ov $OUTPUT_NODATA \
-nodata $OUTPUT_NODATA

#
# ----- setup defaults for color breaks and/or get them from the parameters file
#
CUTOFF_UNITS='Percentile'
LOWER_CUTOFF=10
UPPER_CUTOFF=90

if [ "$(jq -r '.cutoff_units' $PARMS)" ] && [ "$(jq -r '.cutoff_units' $PARMS)" != 'null' ]; then
  CUTOFF_UNITS=$(jq -r '.cutoff_units' $PARMS)
fi

if [ "$(jq -r '.lower_cutoff' $PARMS)" ] && [ "$(jq -r '.lower_cutoff' $PARMS)" != 'null' ]; then
  LOWER_CUTOFF=$(jq -r '.lower_cutoff' $PARMS)
fi

if [ "$(jq -r '.upper_cutoff' $PARMS)" ] && [ "$(jq -r '.upper_cutoff' $PARMS)" != 'null' ]; then
  UPPER_CUTOFF=$(jq -r '.upper_cutoff' $PARMS)
fi

#
# ----- stats, color breaks, write stats to layer in database
#
"$SLATE_PYTHON_APPS_FOLDER"rasterstats.py \
-ccb \
--cutoff_units "$CUTOFF_UNITS" \
--lower_cutoff $LOWER_CUTOFF \
--upper_cutoff $UPPER_CUTOFF \
-t \
-lrowid $(jq -r '.layerRowid' $PARMS) \
-ws \
$WORKING_FOLDER"_t2.tiff"

#
# ----- build array with optional parameters
#       uses array expansion to send to the application
#       not included if they're not used
#
ARRAY_OPTIONAL_PARAMETERS=()

# legend rowid
if [ "$(jq -r '.legendRowid' $PARMS)" ] && [ "$(jq -r '.legendRowid' $PARMS)" != '' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( --legend_rowid )
  ARRAY_OPTIONAL_PARAMETERS+=( "$(jq -r '.legendRowid' $PARMS)" )
fi

#
# ----- make colored version
#
if [ -f "$(jq -r '.legendFilename' $PARMS)" ] || [ "$(jq -r '.outputDataType' $PARMS)" == 'Categorical' ]; then
	echo -e "\nCreating classes and making a colored raster."
	"$SLATE_PYTHON_APPS_FOLDER"color_and_make_classes.py \
	-lrowid $(jq -r '.layerRowid' $PARMS) \
	-a_nodata $OUTPUT_NODATA \
	"${ARRAY_OPTIONAL_PARAMETERS[@]}" \
	$WORKING_FOLDER"_t2.tiff" \
	"$DEST" \
	$(jq -r '.legendFilename' $PARMS)
else
	echo -e "\nCreating a colored raster using gdaldem."
	#
	gdal_translate \
	$WORKING_FOLDER"_t2.tiff" \
	"$DEST"
	#
	gdaldem color-relief \
	-alpha \
  "$DEST" \
	$WORKING_FOLDER"color_breaks.txt" \
	"$DEST_COLORED"
fi

#
# ----- make thumbnails
#
if [ -f "$DEST" ]; then
  if [ -f "$(jq -r '.legendFilename' $PARMS)" ] || [ "$(jq -r '.outputDataType' $PARMS)" == 'Categorical' ]; then
    "$SLATE_SCRIPTS_FOLDER"make_thumbnails.sh $PARMS "$DEST" "$DEST"
  else
    "$SLATE_SCRIPTS_FOLDER"make_thumbnails.sh $PARMS "$DEST" "$DEST_COLORED"
  fi
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