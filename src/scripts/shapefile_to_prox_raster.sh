#!/bin/bash
#
#  slate | Shapfile to a proximity raster.
#
#  Meant to be called by another script, i.e. proximity_from_vector.sh.
#  Setup originally to avoid duplicate code when calculating both external and internal distances.
#  See proximity_from_vector.sh for example usage.
#
#  Command line parameters in order, all required:
#
#    1 = parameters filename, same as passed to calling script
#    2 = lowercase a or b indicating:
#         a = standard single run inverted or not via .invert setting in parameters file
#         b = invert, to be used when a is NOT inverted and b is used to calculate external and internal distances 
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
SUFFIX=$2  

#
# ----- setup common environment variables
#       note . $SETVARS makes them accessible to this environment
#
SETVARS=$(jq -r '.scriptsFolder' $PARMS)'set_common_vars.sh'
. $SETVARS $PARMS

#
# ----- optional parameters
#       uses array expansion to pass them as arguments to a command
#       https://unix.stackexchange.com/questions/444946/how-can-we-run-a-command-stored-in-a-variable
#       https://unix.stackexchange.com/questions/29509/transform-an-array-into-arguments-of-a-command
#
ARRAY_OPTIONAL_PARAMETERS=()

#
# ----- filter with -where
#
if [ "$(jq -r '.filter' $PARMS)" ] && [ "$(jq -r '.filter' $PARMS)" != 'null' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( -where )
  WHERE=$(jq -r '.filter' $PARMS)
  echo 'Filtering with:'
  echo $WHERE
  ARRAY_OPTIONAL_PARAMETERS+=( "$(jq -r '.filter' $PARMS)" )
fi

#
# ----- invert
#
if [ $(jq '.invert' $PARMS) == '"True"' ] || [ $SUFFIX == 'b' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( -i )
fi

# echo Optional parameters array length = "${#ARRAY_OPTIONAL_PARAMETERS[@]}"
# echo "${ARRAY_OPTIONAL_PARAMETERS[*]}"

#
# ----- convert vector to raster
# 
echo -e "\nConverting shapefile to raster."
  gdal_rasterize \
  -burn 1 \
  -ot Byte \
  -a_nodata $NODATA_BYTE \
  -init $NODATA_BYTE \
  -a_srs 'EPSG:'$SRID \
  -te $XMIN $YMIN $XMAX $YMAX \
  -tr $RESOLUTION $RESOLUTION \
  -l "_t0" \
  "${ARRAY_OPTIONAL_PARAMETERS[@]}" \
  $WORKING_FOLDER"_t0.shp" \
  $WORKING_FOLDER"_t1.tiff"
  
#
# ----- remove temporary file if it exists
#       if it exists gdal_proximity will try and use it and give an error if not the same resolution
#
if test -e $WORKING_FOLDER"_t2.tiff"; then
  rm $WORKING_FOLDER"_t2.tiff"
fi

ARRAY_OPTIONAL_PARAMETERS=()
if [ "$(jq -r '.max_distance' $PARMS)" ] && [ "$(jq -r '.max_distance' $PARMS)" != '' ]; then
  ARRAY_OPTIONAL_PARAMETERS+=( -maxdist )
  ARRAY_OPTIONAL_PARAMETERS+=( "$(jq -r '.max_distance' $PARMS)" )
fi

echo -e "\nCalculating proximity raster."
  gdal_proximity.py \
  $WORKING_FOLDER"_t1.tiff" \
  $WORKING_FOLDER"_t2.tiff" \
  -values 1 \
  -distunits GEO \
  -nodata $NODATA_FLOAT32 \
  "${ARRAY_OPTIONAL_PARAMETERS[@]}" \
  -of GTiff
  
#
# ----- replace 0 (on the feature values)
#
echo -e "\nReplacing 0 values (on the feature) with 1."
gdal_calc.py \
--overwrite \
--quiet \
-A $WORKING_FOLDER"/_t2.tiff" \
--outfile=$WORKING_FOLDER"/_t3"$SUFFIX".tiff" \
--calc="where(A>0,A,1)"


 
