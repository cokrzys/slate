#!/bin/bash
#
#  slate | Create deposit signatures.
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
# TODO

#
# ----- show header
#
"$SLATE_SCRIPTS_FOLDER"header.sh 'Creating Signatures'

#
# ----- 
#
"$SLATE_PYTHON_APPS_FOLDER"createsignatures.py \
--resolution_rowid_fk $(jq -r '.resolutionRowid' $PARMS) \
--debug

#
# ----- show environment
#
"$SLATE_SCRIPTS_FOLDER"show_environment.sh $PARMS


 
