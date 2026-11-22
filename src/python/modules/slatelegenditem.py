"""

 slate | Support for legend items and the sp.legend_item table.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import os
import psycopg2
import json

from algaecore import algaeCore
from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblbase import algaeTblBase

from slateconfig import slateConfig
from slateapp import slateApp
from slateproject import slateProject
from slatelegend import slateLegend

class slateLegendItem(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.legend_item'
        self.legend = slateLegend()
        self.int_code = None
        self.text_code = None
        self.sort_order = None
        self.html_color = None
        self.description = None
    
    
        
            
        







