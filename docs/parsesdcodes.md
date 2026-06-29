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

```json
[
  {
    "attribute": "STN_C",
    "num": 413,
    "name": "MRDS STN_C Proximity",
    "filename": "mrds_stn_c_prox",
    "filter": "CODE_LIST ILIKE 'STN_C %' OR CODE_LIST ILIKE '% STN_C' OR CODE_LIST ILIKE '% STN_C %' OR CODE_LIST = 'STN_C'",
    "notes": "STN_C in field CODE_LIST from shapefile mrds.shp."
  }
]
```
