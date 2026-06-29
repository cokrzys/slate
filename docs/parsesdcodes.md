```shell
./parsesdcodes.py \
-rf JSON \
-min_cc 250 \
-max_cc 500 \
-nt 'MRDS {code} Proximity' \
-ft 'mrds_{code}_prox' \
-not '{code} in field {field} from shapefile {shapefile}.' \
mrds.shp \
CODE_LIST
```
