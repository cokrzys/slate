### Basic Example
```shell
./parsesdcodes.py mrds.shp CODE_LIST
```

```console
Read 38952 shapes from mrds.shp.
129 unique codes found for field 'CODE_LIST'.
'AU' 21486
'AG' 9992
'CU' 6472
...
```

### JSON Output Example
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

### Formatting Codes

Replaces argument text with values as noted.

- {code}
- {field}
- {filename}
- {num}
- {shapefile}

