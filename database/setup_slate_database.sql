/**

  slate | PostgreSQL database setup.
  
  Builds the slate data model.
	
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/algae
  
  Versions
  
  Notes specific to this file, may or may not coincide with git comments when added.
  
  2026.08.29 | Beta.

*/

--
-- turn notices off to make the output easier to read
--
SET client_min_messages TO WARNING;

--
-- function to get the version
--
CREATE OR REPLACE FUNCTION slate_database_version() RETURNS varchar LANGUAGE SQL AS
  $$ SELECT CAST('2026.08.29' AS VARCHAR); $$;
  
--
-- add PostGIS support
--
CREATE EXTENSION postgis;
CREATE EXTENSION postgis_topology;
  
-- ============================================================================
--  ref - ref schema additions
-- ============================================================================ 

--
-- ref.geometry_type
--
DROP SEQUENCE IF EXISTS ref.geometry_type_rowid;
DROP TABLE IF EXISTS ref.geometry_type;
CREATE SEQUENCE ref.geometry_type_rowid START 1;
CREATE TABLE ref.geometry_type
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.geometry_type_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  sort_order INTEGER NOT NULL DEFAULT 0,
  html_color VARCHAR NOT NULL default algae_default_color(),
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.geometry_type FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.geometry_type (name, description) VALUES 
  ('3DPoint', 'Points.');
INSERT INTO ref.geometry_type (name, description) VALUES 
  ('3DPolylines', 'Lines.');
INSERT INTO ref.geometry_type (name, description) VALUES 
  ('3DPolygon', 'Polygons.');
  
--
-- ref.output_type
--
DROP SEQUENCE IF EXISTS ref.output_type_rowid;
DROP TABLE IF EXISTS ref.output_type;
CREATE SEQUENCE ref.output_type_rowid START 1;
CREATE TABLE ref.output_type
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.output_type_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  sort_order INTEGER NOT NULL DEFAULT 0,
  min_value NUMERIC,
  max_value NUMERIC,
  nodata_value NUMERIC,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.output_type FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.output_type (name, description) VALUES 
  ('Byte', 'GDAL range: 0 | 255.');
  
INSERT INTO ref.output_type (name, description) VALUES 
  ('UInt16', 'GDAL range: 0 | 65,535.');
  
INSERT INTO ref.output_type (name, description) VALUES 
  ('UInt32', 'GDAL range: 0 | 4,294,967,295.');
  
INSERT INTO ref.output_type (name, description) VALUES 
  ('Float32', 'GDAL range: -3.4E38 | 3.4E38.');
  
INSERT INTO ref.output_type (name, description) VALUES 
  ('Shapefile', 'ESRI shapefile.');
  
--
-- ref.data_type
--
DROP SEQUENCE IF EXISTS ref.data_type_rowid;
DROP TABLE IF EXISTS ref.data_type;
CREATE SEQUENCE ref.data_type_rowid START 1;
CREATE TABLE ref.data_type
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.data_type_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  sort_order INTEGER NOT NULL DEFAULT 0,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.data_type FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.data_type (name, description) VALUES 
  ('Unknown', 'Generic data, unknown pattern.');
  
INSERT INTO ref.data_type (name, description) VALUES 
  ('Sequential', 'Ordered data that progress from low to high, see also: https://colorbrewer2.org/learnmore/schemes_full.html.');
  
INSERT INTO ref.data_type (name, description) VALUES 
  ('Diverging', 'Equal emphasis on mid-range critical values and extremes at both ends of the data range, see also: https://colorbrewer2.org/learnmore/schemes_full.html#diverging.');
  
INSERT INTO ref.data_type (name, description) VALUES 
  ('Categorical', 'Typically discrete values with no implied relationship between classes, see also: https://colorbrewer2.org/learnmore/schemes_full.html#qualitative.');

--
-- ref.data_group
--
DROP SEQUENCE IF EXISTS ref.data_group_rowid;
DROP TABLE IF EXISTS ref.data_group;
CREATE SEQUENCE ref.data_group_rowid START 1;
CREATE TABLE ref.data_group
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.data_group_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  sort_order INTEGER NOT NULL DEFAULT 0,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.data_group FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.data_group (name, description) VALUES 
  ('Other', 'Doesn''t fit in any other group.');

INSERT INTO ref.data_group (name, description) VALUES 
  ('Mask', 'Mask, typically 1 and 0 value, for example a study area mask.');
  
INSERT INTO ref.data_group (name, description) 
  VALUES ('Physical', 'Physical data such as land type or proximity to water.');
  
INSERT INTO ref.data_group (name, description) 
  VALUES ('Cultural', 'Cultural data such as country or ethnic division.');
  
INSERT INTO ref.data_group (name, description) 
  VALUES ('Climatological', 'Climatological data.');
  
INSERT INTO ref.data_group (name, description) 
  VALUES ('Socioeconomic', 'Socioeconomic data.');
  
--
-- ref.units
--
DROP SEQUENCE IF EXISTS ref.units_rowid;
DROP TABLE IF EXISTS ref.units;
CREATE SEQUENCE ref.units_rowid START 1;
CREATE TABLE ref.units
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.units_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  abbreviation VARCHAR NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  sort_order INTEGER NOT NULL DEFAULT 0,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.units FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.units (name, abbreviation, description) 
  VALUES ('millimeters', 'mm', '1/1000 of a meter.');
  
INSERT INTO ref.units (name, abbreviation, description) 
  VALUES ('meters', 'm', 'A meter.');
  
--
-- ref.resolution
--
DROP SEQUENCE IF EXISTS ref.resolution_rowid;
DROP TABLE IF EXISTS ref.resolution;
CREATE SEQUENCE ref.resolution_rowid START 1;
CREATE TABLE ref.resolution
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.resolution_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  folder VARCHAR NOT NULL UNIQUE,
  cell_size_x NUMERIC NOT NULL,
  cell_size_y NUMERIC NOT NULL,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  sort_order INTEGER NOT NULL DEFAULT 0,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.resolution FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- ref.timeframe
--
CREATE SEQUENCE ref.timeframe_rowid START 1;
CREATE TABLE ref.timeframe
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('ref.timeframe_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  name VARCHAR NOT NULL UNIQUE,
  short_name VARCHAR NOT NULL UNIQUE,
  sort_order INTEGER NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON ref.timeframe FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Static', 'Static', 100, '#000000', 'Static, does not apply.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Year', 'Year', 200, '#000000', 'Applies to an entire year, for example a yearly average.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Quarter1', 'Q1', 301, '#000000', 'Applies to the first quarter, for example the average for the quarter.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Quarter2', 'Q2', 302, '#000000', 'Applies to the second quarter, for example the average for the quarter.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Quarter3', 'Q3', 303, '#000000', 'Applies to the third quarter, for example the average for the quarter.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('Quarter4', 'Q4', 304, '#000000', 'Applies to the fourth quarter, for example the average for the quarter.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('January', 'Jan', 1001, '#000000', 'Applies to January, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('February', 'Feb', 1002, '#000000', 'Applies to February, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('March', 'Mar', 1003, '#000000', 'Applies to March, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('April', 'Apr', 1004, '#000000', 'Applies to April, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('May', 'May', 1005, '#000000', 'Applies to May, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('June', 'Jun', 1006, '#000000', 'Applies to June, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('July', 'Jul', 1007, '#000000', 'Applies to July, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('August', 'Aug', 1008, '#000000', 'Applies to August, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('September', 'Sep', 1009, '#000000', 'Applies to September, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('October', 'Oct', 1010, '#000000', 'Applies to October, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('November', 'Nov', 1011, '#000000', 'Applies to November, for example the average for the month.');
INSERT INTO ref.timeframe (name, short_name, sort_order, html_color, description) 
  VALUES ('December', 'Dec', 1012, '#000000', 'Applies to December, for example the average for the month.');
  
--
-- drop anything that exists
--
DROP SCHEMA IF EXISTS sp CASCADE;
DROP SCHEMA IF EXISTS temp CASCADE;

--
-- create schemas
--
CREATE SCHEMA sp;
CREATE SCHEMA temp;

-- ============================================================================
--  sp - spatial schema
-- ============================================================================ 

--
-- sp.project
--
DROP TABLE IF EXISTS sp.project;
DROP SEQUENCE IF EXISTS sp.project_rowid;
CREATE SEQUENCE sp.project_rowid START 1;
CREATE TABLE sp.project
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.project_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status, 
  user_rowid_fk INTEGER NOT NULL REFERENCES core.user, 
  name VARCHAR NOT NULL UNIQUE,
  abbreviation VARCHAR NOT NULL,
  folder VARCHAR NOT NULL UNIQUE,
  public VARCHAR NOT NULL CONSTRAINT public_constraint CHECK ( public = 'Yes' OR public = 'No') DEFAULT 'No',
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  copyright VARCHAR,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(user_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.project FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();  
ALTER TABLE sp.project ADD CONSTRAINT sp_project_unique_user_abbreviation UNIQUE (user_rowid_fk, abbreviation);


--
-- sp.place
--
DROP TABLE IF EXISTS sp.place;
DROP SEQUENCE IF EXISTS sp.place_rowid;
CREATE SEQUENCE sp.place_rowid START 1;
CREATE TABLE sp.place
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.place_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status, 
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  name VARCHAR NOT NULL UNIQUE,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  abbreviation VARCHAR NOT NULL UNIQUE,
  location GEOGRAPHY(Point, 4326),
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.place FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
ALTER TABLE sp.place ADD CONSTRAINT unique_abbreviation UNIQUE (project_rowid_fk, abbreviation);
    
--
-- sp.shapefile
--
DROP TABLE IF EXISTS sp.shapefile;
DROP SEQUENCE IF EXISTS sp.shapefile_rowid;
CREATE SEQUENCE sp.shapefile_rowid START 1;
CREATE TABLE sp.shapefile
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.shapefile_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(), 
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  srid_fk INTEGER NOT NULL REFERENCES spatial_ref_sys,
  user_rowid_fk INTEGER NOT NULL REFERENCES core.user, 
  geometry_type_rowid_fk INTEGER NOT NULL REFERENCES ref.geometry_type,
  timeframe_rowid_fk INTEGER NOT NULL REFERENCES ref.timeframe,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  name VARCHAR NOT NULL UNIQUE,
  extent GEOGRAPHY(Polygon),
  source_filename VARCHAR NOT NULL,
  source_url VARCHAR,
  year INTEGER NOT NULL,
  style VARCHAR,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.shapefile FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.geoprocess
--
DROP TABLE IF EXISTS sp.geoprocess;
DROP SEQUENCE IF EXISTS sp.geoprocess_rowid;
CREATE SEQUENCE sp.geoprocess_rowid START 1;
CREATE TABLE sp.geoprocess
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.geoprocess_rowid'),
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  process_rowid_fk INTEGER REFERENCES core.process,
  output_type_rowid_fk INTEGER NOT NULL REFERENCES ref.output_type,
  data_type_rowid_fk INTEGER NOT NULL REFERENCES ref.data_type,
  data_group_rowid_fk INTEGER NOT NULL REFERENCES ref.data_group,
  units_rowid_fk INTEGER REFERENCES ref.units,
  name VARCHAR NOT NULL,
  relative_folder VARCHAR NOT NULL,
  php_class VARCHAR NOT NULL,
  command VARCHAR NOT NULL,
  sequence INTEGER NOT NULL DEFAULT 100,
  parameters VARCHAR,
  num_decimals INTEGER,
  batch_parameters VARCHAR,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.geoprocess FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
    
--
-- sp.study_area
--
DROP TABLE IF EXISTS sp.study_area;
DROP SEQUENCE IF EXISTS sp.study_area_rowid;
CREATE SEQUENCE sp.study_area_rowid START 1;
CREATE TABLE sp.study_area
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.study_area_rowid'),
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  shapefile_rowid_fk INTEGER NOT NULL REFERENCES sp.shapefile,
  user_rowid_fk INTEGER NOT NULL REFERENCES core.user,
  srid_fk INTEGER NOT NULL REFERENCES spatial_ref_sys,
  geoprocess_rowid_fk INTEGER REFERENCES sp.geoprocess,
  place_rowid_fk INTEGER REFERENCES sp.place,
  min_x NUMERIC NOT NULL,
  min_y NUMERIC NOT NULL,
  max_x NUMERIC NOT NULL,
  max_y NUMERIC NOT NULL,
  buffer NUMERIC NOT NULL,
  low_resolution NUMERIC NOT NULL,
  medium_resolution NUMERIC NOT NULL,
  high_resolution NUMERIC NOT NULL,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.study_area FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
   
--
-- sp.layer
--
DROP TABLE IF EXISTS sp.layer;
DROP SEQUENCE IF EXISTS sp.layer_rowid;
CREATE SEQUENCE sp.layer_rowid START 1;
CREATE TABLE sp.layer
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.layer_rowid'),
  geoprocess_rowid_fk INTEGER NOT NULL REFERENCES sp.geoprocess,
  resolution_rowid_fk INTEGER NOT NULL REFERENCES ref.resolution,
  filename VARCHAR NOT NULL,
  num_cols INTEGER,
  num_rows INTEGER,
  num_nodata_cells BIGINT,
  data_format VARCHAR,
  data_min NUMERIC,
  data_max NUMERIC,
  data_mean NUMERIC,
  data_stddev NUMERIC,
  data_q1 NUMERIC,
  data_median NUMERIC,
  data_q3 NUMERIC,
  sig_lower_cutoff NUMERIC,
  sig_upper_cutoff NUMERIC,
  last_updated_utc TIMESTAMP,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(geoprocess_rowid_fk, resolution_rowid_fk)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.layer FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.signature
--
DROP TABLE IF EXISTS sp.signature;
DROP SEQUENCE IF EXISTS sp.signature_rowid;
CREATE SEQUENCE sp.signature_rowid START 1;
CREATE TABLE sp.signature
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.signature_rowid'),
  place_rowid_fk INTEGER NOT NULL REFERENCES sp.place,
  layer_rowid_fk INTEGER NOT NULL REFERENCES sp.layer,
  numeric_val NUMERIC NOT NULL,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(place_rowid_fk, layer_rowid_fk)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.signature FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.class
--
DROP TABLE IF EXISTS sp.class;
DROP SEQUENCE IF EXISTS sp.class_rowid;
CREATE SEQUENCE sp.class_rowid START 1;
CREATE TABLE sp.class
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.class_rowid'),
  layer_rowid_fk INTEGER NOT NULL REFERENCES sp.layer,
  raster_value INTEGER NOT NULL,
  num_values BIGINT NOT NULL,
  code VARCHAR NOT NULL,
  html_color VARCHAR NOT NULL DEFAULT algae_default_color(),
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(layer_rowid_fk, raster_value)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.class FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.file, references to uploaded single files
--
DROP TABLE IF EXISTS sp.file;
DROP SEQUENCE IF EXISTS sp.file_rowid;
CREATE SEQUENCE sp.file_rowid START 1;
CREATE TABLE sp.file
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.file_rowid'),
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  data_group_rowid_fk INTEGER NOT NULL REFERENCES ref.data_group,
  filename VARCHAR NOT NULL,
  size_bytes BIGINT NOT NULL,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk, filename)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.file FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.map, map, typically composed of styled layers
--
DROP TABLE IF EXISTS sp.map;
DROP SEQUENCE IF EXISTS sp.map_rowid;
CREATE SEQUENCE sp.map_rowid START 1;
CREATE TABLE sp.map
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.map_rowid'),
  record_status_rowid_fk INTEGER NOT NULL REFERENCES ref.record_status DEFAULT algae_active_rowid(),
  project_rowid_fk INTEGER NOT NULL REFERENCES sp.project,
  name VARCHAR NOT NULL,
  center GEOGRAPHY(Point),
  zoom_level INTEGER,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(project_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.map FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();
  
--
-- sp.map_layer, a styled layer on a map
--
DROP TABLE IF EXISTS sp.map_layer;
DROP SEQUENCE IF EXISTS sp.map_layer_rowid;
CREATE SEQUENCE sp.map_layer_rowid START 1;
CREATE TABLE sp.map_layer
(
  rowid INTEGER PRIMARY KEY DEFAULT nextval('sp.map_layer_rowid'),
  map_rowid_fk INTEGER NOT NULL REFERENCES sp.map,
  name VARCHAR NOT NULL,
  shapefile_rowid_fk INTEGER REFERENCES sp.shapefile,
  layer_rowid_fk INTEGER REFERENCES sp.layer,
  initial_state VARCHAR NOT NULL CHECK (initial_state = 'On' OR initial_state = 'Off'),
  filter VARCHAR,
  styling VARCHAR,
  queryable VARCHAR NOT NULL CHECK ( queryable = 'Yes' OR queryable = 'No') DEFAULT 'Yes',
  query_template VARCHAR,
  description VARCHAR,
  timestamp_loaded_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  timestamp_modified_utc TIMESTAMP NOT NULL DEFAULT current_timestamp,
  UNIQUE(map_rowid_fk, name)
);
CREATE TRIGGER update_modified BEFORE UPDATE
  ON sp.map_layer FOR EACH ROW EXECUTE PROCEDURE
  algae_update_modified_column();

--
-- cleanup, refresh stats
--
VACUUM ANALYZE;
  

  
  
  
  
