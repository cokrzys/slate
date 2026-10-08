"""

 Slate | Class and sp.class support.
 
 A layer is a reference to a raster and associated metadata.

 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys

from algaedb import algaeDB
from algaetblbase import algaeTblBase

# from slatelayer import slateLayer

class slateClass(algaeTblBase):

    def __init__(self):
    #------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.class'
        # self.layer = slateLayer()
        self.raster_value = None
        self.num_values = None
        self.code = None
        self.html_color = None
        self.description = None
    
    
    
    