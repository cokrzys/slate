--
--
--
CREATE OR REPLACE FUNCTION slate_layer_histogram(layer_rowid_fk INTEGER) RETURNS TABLE 
(
  bin INTEGER,
  num BIGINT,
  cumulative_num BIGINT,
  total_num BIGINT,
  bin_min NUMERIC,
  bin_max NUMERIC,
  pct NUMERIC,
  cumulative_pct NUMERIC
)
AS $$
  SELECT h.bin, h.num, h.cumulative_num, h.total_num, b.bin_min, b.bin_max,
    ROUND(CAST(h.num AS NUMERIC) / CAST(h.total_num AS NUMERIC) * 100, 2) AS pct,
    ROUND(CAST(h.cumulative_num AS NUMERIC) / CAST(h.total_num AS NUMERIC) * 100, 2) AS cumulative_pct
  FROM
  (
    SELECT *,
      SUM(num) OVER (ORDER BY bin) AS cumulative_num
    FROM
    (
    SELECT ROW_NUMBER() OVER () AS bin, 
      num,
      SUM(num) OVER () AS total_num
    FROM 
    (
      SELECT UNNEST(histogram) AS num
      FROM sp.layer
      WHERE rowid = layer_rowid_fk
    ) t1
    ) t2
  ) h
  INNER JOIN
  (
    SELECT *
    FROM
    (
    SELECT ROW_NUMBER() OVER () AS bin,
      edge AS bin_min,
      LEAD(edge, 1) OVER () AS bin_max
    FROM 
    (
      SELECT UNNEST(bin_edges) AS edge
      FROM sp.layer
      WHERE rowid = layer_rowid_fk
    ) t1
    ) t2
    WHERE bin_max IS NOT NULL
  ) b ON h.bin = b.bin
$$ LANGUAGE sql;

--
-- DOES NOT WORK
--
CREATE OR REPLACE FUNCTION slate_relationship_bin_data(place_set_rowid_fk_var INTEGER, layer_rowid_fk_var INTEGER) 
  RETURNS TABLE 
(
  bin BIGINT,
  bin_min NUMERIC,
  bin_max NUMERIC,
  cells NUMERIC,
  signatures NUMERIC,
  quantity NUMERIC
)
LANGUAGE plpgsql AS
$func$
BEGIN
	RETURN QUERY(SELECT ROW_NUMBER() OVER () AS bin, b.*
	FROM
	(
	  SELECT 
		  UNNEST(bin_data[1:][1:1]) AS bin_min,
		  UNNEST(bin_data[1:][2:2]) AS bin_max,
		  UNNEST(bin_data[1:][3:3]) AS cells,
		  UNNEST(bin_data[1:][4:4]) AS signatures,
		  UNNEST(bin_data[1:][5:5]) AS quantity
	  FROM sp.relationship
	  WHERE place_set_rowid_fk = place_set_rowid_fk_var AND layer_rowid_fk = layer_rowid_fk_var
	) b);
END
$func$;

