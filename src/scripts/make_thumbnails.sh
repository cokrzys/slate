#!/bin/bash
#
#  slate | Make thumbnails.
#
#  @author    Brian Krzys (brian.krzys@rtspatial.com)
#  @copyright (c) 2026 RTSpatial Ltd.
#  @license   SPDX-License-Identifier: MIT
#  @link      https://github.com/cokrzys/slate
#

#
# ----- JSON parameter file from the command line
#
PARMS=$1
SOURCE=$2
SOURCE_COLORED=$3

SOURCE_PATH=`dirname $SOURCE`
SOURCE_FILENAME=`basename $SOURCE ".tiff"`
THUMB=$SOURCE_PATH"/"$SOURCE_FILENAME"_thumb.png"
WIDTH=$(jq '.thumb_x' $PARMS)
HEIGHT=$(jq '.thumb_y' $PARMS)

echo -e "\nCreating thumbnails."
echo $THUMB" at "$WIDTH"x"$HEIGHT
convert -quiet $SOURCE_COLORED -resize $WIDTH"x"$HEIGHT -transparent "rgb(0, 0, 0)" $THUMB