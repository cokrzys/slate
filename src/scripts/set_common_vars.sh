#!/bin/bash
#
# Set common environment variables.
# This is typically called by another script like . set_common_vars.sh parms.json.
# See: https://stackoverflow.com/questions/496702/can-a-shell-script-set-environment-variables-of-the-calling-shell
# 

PARMS=$1

SLATE_SCRIPTS_FOLDER=$(jq '.scriptsFolder' $PARMS)
SLATE_PYTHON_APPS_FOLDER=$(jq '.pythonAppsFolder' $PARMS)
SLATE_PALETTES_FOLDER=$(jq '.palettesFolder' $PARMS)
WORKING_FOLDER=$(jq '.workingFolder' $PARMS)
SRID=$(jq '.srid_fk' $PARMS)
XMIN=$(jq '.min_x' $PARMS)
XMAX=$(jq '.max_x' $PARMS)
YMIN=$(jq '.min_y' $PARMS)
YMAX=$(jq '.max_y' $PARMS)
RESOLUTION=$(jq '.resolution' $PARMS)
RESOLUTION_NAME=$(jq '.resolutionName' $PARMS)
RESOLUTION_FOLDER=$(jq '.resolutionFolder' $PARMS)
NODATA_BYTE=$(jq '.noDataByte' $PARMS)
NODATA_FLOAT32=$(jq '.noDataFloat32' $PARMS)
# PROCESS_ROWID=$(jq '.process.rowid' $PARMS)

#
# ----- change "/ebs1/sp/wdgm/gp00/gp0005/" to /ebs1/sp/wdgm/gp00/gp0005/
#       see: https://tecadmin.net/bash-remove-double-quote-string/
#
SLATE_SCRIPTS_FOLDER=`sed -e 's/^"//' -e 's/"$//' <<<"$SLATE_SCRIPTS_FOLDER"`
SLATE_PYTHON_APPS_FOLDER=`sed -e 's/^"//' -e 's/"$//' <<<"$SLATE_PYTHON_APPS_FOLDER"`
SLATE_PALETTES_FOLDER=`sed -e 's/^"//' -e 's/"$//' <<<"$SLATE_PALETTES_FOLDER"`
WORKING_FOLDER=`sed -e 's/^"//' -e 's/"$//' <<<"$WORKING_FOLDER"`
RESOLUTION_NAME=`sed -e 's/^"//' -e 's/"$//' <<<"$RESOLUTION_NAME"`
RESOLUTION_FOLDER=`sed -e 's/^"//' -e 's/"$//' <<<"$RESOLUTION_FOLDER"`



