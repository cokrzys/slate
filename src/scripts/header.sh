#!/bin/bash
#
#  slate | Consistent header message that shows when a script started.
#
#  @author    Brian Krzys (brian.krzys@rtspatial.com)
#  @copyright (c) 2026 RTSpatial Ltd.
#  @license   SPDX-License-Identifier: MIT
#  @link      https://github.com/cokrzys/slate
#

MESSAGE=$1

DATE=$(date '+%Y-%m-%d %H:%M:%S UTC')
# echo ''
echo '---------------------------------------------------------------------------------'
echo ' '$DATE' | '$MESSAGE
echo '---------------------------------------------------------------------------------'
echo ''