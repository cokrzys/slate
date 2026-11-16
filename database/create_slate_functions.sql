

SELECT ROW_NUMBER() OVER () AS bin, b.*
FROM
(
  SELECT 
	  UNNEST(bin_data[1:][1:1]) AS bin_min,
	  UNNEST(bin_data[1:][2:2]) AS bin_max,
	  UNNEST(bin_data[1:][3:3]) AS cells,
	  UNNEST(bin_data[1:][4:4]) AS signatures,
	  UNNEST(bin_data[1:][5:5]) AS quantity
  FROM sp.relationship
  WHERE layer_rowid_fk = 54
) b