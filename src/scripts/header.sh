#!/bin/bash
#
# Consistent header message that shows when a script started.
#

MESSAGE=$1

DATE=$(date '+%Y-%m-%d %H:%M:%S UTC')
# echo ''
echo '---------------------------------------------------------------------------------'
echo ' '$DATE' | '$MESSAGE
echo '---------------------------------------------------------------------------------'
echo ''