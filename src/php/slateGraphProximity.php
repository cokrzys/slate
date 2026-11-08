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
  
  public $field;
  public $layer;
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    
    $this->field = new algaeTblField();
    $this->layer = new slateLayer();
    
    $this->field->rowid = algaeForm::getDefaultValue('field', '', slateGraphProximity::PARAMETERS_PREFIX . 'Field');
    $this->field->read_row_from_database_with_rowid($this->field->rowid);
    
    $this->layer->rowid = algaeForm::getDefaultValue('layer', '', slateGraphProximity::PARAMETERS_PREFIX . 'Layer');
    $this->layer->read_row_from_database_with_rowid($this->layer->rowid);
    
    $this->sql = "SELECT t2.*,
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
    
    $this->sql_parms = array($this->field->rowid, $this->layer->rowid, $this->layer->resolution->rowid);
    
    $this->mainTitle = $this->field->name . ' vs ' . $this->layer->geoprocess->name;
    $this->xAxis->varNum = 2;
    $this->xAxis->title = 'Distance (meters)';
    $this->yAxis->varNum = 7;
    $this->yAxis->title = 'Cumulative % ' . $this->field->name;
    $this->marginBottom += 5;
    $this->marginLeft += 5;
  }
  
  protected function addOptionItems()
  // --------------------------------------------------------------------------
  {
    algaeTable::writeTwoColumns('Layer', $this->layer->selectProximityLayers('layer', $this->layer->geoprocess->name), False);
    algaeTable::writeTwoColumns('Field', $this->field->selectNumericFields('field', $this->field->name), False);
  }
  
  protected function loadData()
  // --------------------------------------------------------------------------
  {
    if ( ($this->layer->rowid > 0) && ($this->field->rowid > 0) )
    {
      return parent::loadData();
    }
    else
    {
      algaeApp::errorMessage('Missing required parameter(s).');
    }
    return False;
  }
  
}






