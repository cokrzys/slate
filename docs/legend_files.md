## Legend Files

Parse space delimited codes from a shapefile field.
 
Reads a space delimited codes field with data like "NB TA BE ABR_G MIC FLD" and parses it into a unique set of individual items.  Originally written to support using the space delimited commodity lists in MRDS data.
 
https://mrdata.usgs.gov/mrds/commodity-codes.html

```json
{
  "legend": [
    {
      "numCode": 0,
      "charCode": "0",
      "red": 103,
      "green": 185,
      "blue": 225,
      "description": "Pre-Cenozoic Basement Rocks"
    },
    {
      "numCode": 1,
      "charCode": "1",
      "red": 255,
      "green": 132,
      "blue": 65,
      "description": "Cenozoic Volcanic Rocks"
    },
    {
      "numCode": 5,
      "charCode": "5",
      "red": 255,
      "green": 250,
      "blue": 226,
      "description": "Cenozoic Sedimentary Rocks"
    }
  ]
}
```

