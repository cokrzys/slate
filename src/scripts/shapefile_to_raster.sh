#!/bin/bash
#
#  slate | 
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
if [ $(jq '.invert' $PARMS) == '"True"' ]; then
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

 
