<?php

/**

  slate | Proximity graph.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateGraphProximity extends algaeGraphLine
{
  
  CONST PARAMETERS_PREFIX = 'proximityGraph';
  CONST MAX_FIELDS = 3;
  
  public $fields_array;
  public $layer;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    
    
    $this->fields_array = array();
    for ($i = 0; $i < slateGraphProximity::MAX_FIELDS; $i++) 
    {
      $this->fields_array[] = new algaeTblField();
      // echo 'DEBUG: ', count($this->fields_array), '<p />';
      $id = 'field' . strval($i);
      $this->fields_array[$i]->read_row_from_database_with_rowid(algaeForm::getDefaultValue($id,
        slateGraphProximity::PARAMETERS_PREFIX . $id));
    }
    $this->layer = new slateLayer();
    $this->layer->rowid = algaeForm::getDefaultValue('layer', '', slateGraphProximity::PARAMETERS_PREFIX . 'Layer');
    $this->layer->read_row_from_database_with_rowid($this->layer->rowid);
    
    $this->sql = "SELECT dist,
                    ROUND(CAST(cumulative_val AS NUMERIC) / CAST(total AS NUMERIC) * 100, 4) AS pct_of_total
                  FROM
                  (
                    SELECT t1.*,
                  	SUM(val) OVER (ORDER BY dist ASC, val DESC) AS cumulative_val,
                  	ROW_NUMBER() OVER (ORDER BY dist ASC, val DESC) AS num,
                  	SUM(VAL) OVER () AS total
                    FROM
                    (
                    	SELECT s.place_rowid_fk, p.name, s.numeric_val AS dist, 
                    	  CASE WHEN pd.integer_val IS NOT NULL THEN pd.integer_val ELSE 0 END AS val
                    	FROM sp.signature s
                    	INNER JOIN sp.place p ON s.place_rowid_fk = p.rowid
                    	LEFT OUTER JOIN sp.place_data pd ON pd.place_rowid_fk = p.rowid AND pd.field_rowid_fk = $1
                    	WHERE s.layer_rowid_fk = $2 AND s.resolution_rowid_fk = $3
                    ) t1
                  ) t2";
    
    // $this->sql_parms = array($this->field->rowid, $this->layer->rowid, $this->layer->resolution->rowid);
    
    // $this->mainTitle = $this->field->name . ' vs ' . $this->layer->geoprocess->name;
    $this->xAxis->varNum = 0;
    $this->xAxis->title = 'Distance (meters)';
    $this->yAxis->varNum = 1;
    // $this->yAxis->title = 'Cumulative % ' . $this->field->name;
    $this->marginBottom += 5;
    $this->marginLeft += 5;
  }
  
  protected function addOptionItems()
  // --------------------------------------------------------------------------
  {
    algaeTable::writeTwoColumns('Layer', $this->layer->selectProximityLayers('layer', $this->layer->geoprocess->name), False);
    
    for ($i = 0; $i < slateGraphProximity::MAX_FIELDS; $i++)
    {
      $id = 'field' . strval($i);
      algaeTable::writeTwoColumns('Field', 
        $this->fields_array[$i]->selectNumericFields($id, $this->fields_array[$i]->name), False);
    }
    
  }
  
  protected function loadFieldData($field)
  // --------------------------------------------------------------------------
  {
    if ( (isset($field->rowid)) && ($field->rowid > 0) )
    {
      $sql = $this->sql;
      $parms = array($field->rowid, $this->layer->rowid, $this->layer->resolution->rowid);
      $data = algaeDB::getArray($sql, $parms);
      if ( (isset($data)) && (count($data) > 0) )
      {
        algaeCore::addJavaScript($this->javaScriptVar . ".addData(" . json_encode($data, JSON_NUMERIC_CHECK) . ", '" . 
          $field->name . "','" . '#0000FF' . "');");
        return True;
      }
    }
    return False;
  }
  
  protected function loadDistanceData()
  // --------------------------------------------------------------------------
  {
    $sql = "SELECT bin_max, cumulative_pct FROM layer_histogram($1)";
    $data = algaeDB::getArray($sql, array($this->layer->rowid));
    if ( (isset($data)) && (count($data) > 0) )
    {
      algaeCore::addJavaScript($this->javaScriptVar . ".addData(" . json_encode($data, JSON_NUMERIC_CHECK) . ", '" . 'Layer' . "','" . '#0000FF' . "');");
      return True;
    }
    return False;
  }
  
  protected function loadData()
  // --------------------------------------------------------------------------
  {
    if ($this->layer->rowid > 0)
    {
      $ret = False;
      
      for ($i = 0; $i < slateGraphProximity::MAX_FIELDS; $i++)
      {
        foreach ($this->fields_array as $field)
        {
          $this->loadFieldData($field);
        }
      }
      
      $ret = $this->loadDistanceData();
      return $ret;
    }
    else
    {
      algaeApp::errorMessage('Missing required parameter(s).');
    }
    return False;
  }
  
}






