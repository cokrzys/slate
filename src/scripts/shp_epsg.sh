#!/bin/bash
#
#  slate | Get shapefile epsg using ogrinfo.
#
#  @author    Brian Krzys (brian.krzys@rtspatial.com)
#  @copyright (c) 2026 RTSpatial Ltd.
#  @license   SPDX-License-Identifier: MIT
#  @link      https://github.com/cokrzys/slate
#

SHPFILE=$1
BASE=`basename $SHPFILE .shp`

#
# ----- gets the last line of every line containing EPSG
#       returns something like ID["EPSG",26911]]
#
EPSG=`ogrinfo -so $SHPFILE $BASE | grep EPSG | tail -n 1`
echo $EPSG