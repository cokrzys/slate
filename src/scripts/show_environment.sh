#!/bin/bash
#
#  slate | Show the environment.
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
echo $SETVARS
. $SETVARS $PARMS

echo ''
echo '================================================================================='
echo 'PYTHONPATH                : '$PYTHONPATH
echo 'RTSPATIAL_CONFIG_PATH     : '$RTSPATIAL_CONFIG_PATH
echo 'SLATE_SCRIPTS_FOLDER      : '$SLATE_SCRIPTS_FOLDER
echo 'SLATE_PYTHON_APPS_FOLDER  : '$SLATE_PYTHON_APPS_FOLDER
echo 'SLATE_PALETTES_FOLDER     : '$SLATE_PALETTES_FOLDER
echo 'WORKING_FOLDER            : '$WORKING_FOLDER
echo 'NODATA_BYTE               : '$NODATA_BYTE
echo 'NODATA_FLOAT32            : '$NODATA_FLOAT32
echo 'SRID                      : '$SRID
echo 'XMIN                      : '$XMIN
echo 'XMAX                      : '$XMAX
echo 'YMIN                      : '$YMIN
echo 'YMAX                      : '$YMAX
echo 'RESOLUTION                : '$RESOLUTION
echo 'RESOLUTION_FOLDER         : '$RESOLUTION_FOLDER
echo 'RESOLUTION_NAME           : '$RESOLUTION_NAME
echo '================================================================================='
