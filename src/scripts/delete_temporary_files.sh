#!/bin/bash
#
#  slate | Delete temporary files and directories (_t*)
#
#  $1 = folder to delete files from
#
#  Only works if there are temporary files in the starting folder.
#
#  @author    Brian Krzys (brian.krzys@rtspatial.com)
#  @copyright (c) 2026 RTSpatial Ltd.
#  @license   SPDX-License-Identifier: MIT
#  @link      https://github.com/cokrzys/slate
#

if ls $1'/_t'* 1> /dev/null 2>&1; then
  rm -R $1'/_t'*
fi