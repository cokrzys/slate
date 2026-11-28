#!/bin/bash
#
#  slate | Export data from PostGIS to a shapefile.
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
DEST_FILENAME=$(jq -r '.output_shapefile' $PARMS)
DEST=$WORKING_FOLDER$DEST_FILENAME
DEST_BASE=`basename $DEST .shp`
ZIP_FILE=$WORKING_FOLDER$DEST_BASE".zip"

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'PostGIS to Shapefile'
echo "Output filename = "$DEST
echo "Output base = "$DEST_BASE
echo "SQL Query = "$(jq '.sql' $PARMS)

#
# ----- necessary to remove the extra set of double quotes
#       see set_common_vars.sh for more information
#
SQL=$(jq '.sql' $PARMS)
SQL=`sed -e 's/^"//' -e 's/"$//' <<<"$SQL"`

#
# ----- export
#
pgsql2shp -f $DEST -h localhost -p 5432 -u postgres slate "$SQL"

#
# ----- remove zip file if it exists
#
if test -e $ZIP_FILE; then
  rm $ZIP_FILE
fi

#
# ----- put shapefile parts into a zip file
#
zip -j $ZIP_FILE $WORKING_FOLDER$DEST_BASE*

#
# ----- show environment
#
"$SLATE_SCRIPTS_FOLDER"show_environment.sh $PARMS


 
