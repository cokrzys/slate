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
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
    parent::__construct();
    
    $this->sql = "SELECT t.*,
                    SUM(val) OVER (ORDER BY dist ASC, val DESC) AS cumulative_val,
                    ROW_NUMBER() OVER (ORDER BY dist ASC, val DESC) AS num
                  FROM
                  (
                    SELECT s.place_rowid_fk, p.name, s.numeric_val AS dist, 
                  	CASE WHEN pd.integer_val IS NOT NULL THEN pd.integer_val ELSE 0 END AS val
                    FROM sp.signature s
                    INNER JOIN sp.place p ON s.place_rowid_fk = p.rowid
                    LEFT OUTER JOIN sp.place_data pd ON pd.place_rowid_fk = p.rowid AND pd.field_rowid_fk = 3
                    WHERE s.layer_rowid_fk = 6 AND s.resolution_rowid_fk = 2
                  ) t
                  -- WHERE t.val >= 10000
                  ORDER BY 6";
    $this->xAxis->varNum = 2;
    $this->yAxis->varNum = 4;
  }
  
}






