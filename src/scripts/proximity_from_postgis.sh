#!/bin/bash
#
#  slate | Create a proximity raster from a PostGIS query.
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
DEST_FILENAME=$(jq -r '.layerFilename' $PARMS)
DEST=$WORKING_FOLDER$DEST_FILENAME
DEST_BASE=`basename $DEST .tiff`
DEST_COLORED=$WORKING_FOLDER$DEST_BASE"_colored.png"

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'Proximity from PostGIS Query'
echo "Output filename = "$DEST
echo "SQL Query = "$(jq '.sql' $PARMS)

#
# ----- update layer calc begin timestamp
#
"$SLATE_PYTHON_APPS_FOLDER"updatelayerfiledates.py \
--layer_rowid_fk $(jq -r '.layerRowid' $PARMS) \
--column begin

#
# ----- optional parameters
#       uses array expansion to pass them as arguments to a command
#       https://unix.stackexchange.com/questions/444946/how-can-we-run-a-command-stored-in-a-variable
#       https://unix.stackexchange.com/questions/29509/transform-an-array-into-arguments-of-a-command
#
ARRAY_OPTIONAL_PARAMETERS=()

#
# ----- invert
#
if [ $(jq '.invert' $PARMS) == '"True"' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( -i )
fi

# echo Optional parameters array length = "${#ARRAY_OPTIONAL_PARAMETERS[@]}"
# echo "${ARRAY_OPTIONAL_PARAMETERS[*]}"

#
# ----- necessary to remove the extra set of double quotes
#       see set_common_vars.sh for more information
#
SQL=$(jq '.sql' $PARMS)
SQL=`sed -e 's/^"//' -e 's/"$//' <<<"$SQL"`

#
# ----- convert postgis to raster
# 
echo -e "\nConverting PostGIS query to raster."
  gdal_rasterize \
  -burn 1 \
  -ot Byte \
  -a_nodata $NODATA_BYTE \
  -init $NODATA_BYTE \
  -a_srs 'EPSG:'$SRID \
  -te $XMIN $YMIN $XMAX $YMAX \
  -tr $RESOLUTION $RESOLUTION \
  -sql "$SQL" \
  "${ARRAY_OPTIONAL_PARAMETERS[@]}" \
  PG:'host=localhost user=postgres dbname=slate' \
  $WORKING_FOLDER"_t1.tiff"

#
# ----- remove temporary file if it exists
#       if it exists gdal_proximity will try and use it and give an error if not the same resolution
#
if test -e $WORKING_FOLDER"_t2.tiff"; then
  rm $WORKING_FOLDER"_t2.tiff"
fi

#
# ----- proximity raster, two versions with and without max distance
#
if [ -z "$(jq -r '.max_distance' $PARMS)" ]
then
  echo -e "\nCalculating proximity raster."
  gdal_proximity.py \
  $WORKING_FOLDER"_t1.tiff" \
  $WORKING_FOLDER"_t2.tiff" \
  -values 1 \
  -distunits GEO \
  -nodata $NODATA_FLOAT32 \
  -of GTiff
else
  echo -e "\nCalculating proximity raster with max distance."
  gdal_proximity.py \
  $WORKING_FOLDER"_t1.tiff" \
  $WORKING_FOLDER"_t2.tiff" \
  -values 1 \
  -distunits GEO \
  -nodata $NODATA_FLOAT32 \
  -maxdist $(jq -r '.max_distance' $PARMS) \
  -of GTiff
fi

#
# ----- replace 0 (on the feature values)
#
echo -e "\nReplacing 0 values (on the feature) with 1."
gdal_calc.py \
--overwrite \
--quiet \
-A $WORKING_FOLDER"/_t2.tiff" \
--outfile=$WORKING_FOLDER"/_t3.tiff" \
--calc="where(A>0,A,1)"

#
# ----- apply study area mask
#
echo -e "\nApplying study area mask."
"$SLATE_PYTHON_APPS_FOLDER"mask.py \
$(jq -r '.maskFilename' $PARMS) \
$WORKING_FOLDER"_t3.tiff" \
"$DEST" \
-mv $NODATA_BYTE \
-ov $NODATA_FLOAT32 \
-nodata $NODATA_FLOAT32

#
# ----- stats on final raster
#
"$SLATE_PYTHON_APPS_FOLDER"rasterstats.py \
-ccb \
-lrowid $(jq -r '.layerRowid' $PARMS) \
-ws \
"$DEST"

#
# ----- proximity histogram
#
"$SLATE_PYTHON_APPS_FOLDER"proximitystats.py \
--layer_rowid_fk $(jq -r '.layerRowid' $PARMS)

#
# ----- make colored version
#
echo -e "\nMaking colored version of proximity raster."
# rm $DEST_BASE".png"
gdaldem color-relief \
-alpha \
"$DEST" \
$WORKING_FOLDER"color_breaks.txt" \
"$DEST_COLORED"

#
# ----- make thumbnails
#
if [ -f "$DEST" ]
then
  "$SLATE_SCRIPTS_FOLDER"make_thumbnails.sh $PARMS "$DEST" "$DEST_COLORED"
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

 
