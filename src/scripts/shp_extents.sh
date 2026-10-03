#!/bin/bash
#
# Get the extents of a shapefile.
#
# Usage: ./shp_extents test.shp
# 

SHPFILE=$1
BASE=`basename $SHPFILE .shp`

EXTENT=`ogrinfo -so $SHPFILE $BASE | grep Extent \
| sed 's/Extent: //g' | sed 's/(//g' | sed 's/)//g' \
| sed 's/ - /, /g'`
EXTENT=`echo $EXTENT | awk -F ',' '{print $1 " " $3 " " $2 " " $4}'`
#
# ----- labels removed to make it easier to use the output in another script
#
# echo 'minx maxx miny maxy'
echo $EXTENT