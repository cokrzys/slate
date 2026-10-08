"""

  slate | Support for data type and the ref.data_type table.
 
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

"""

import sys
import psycopg2
import json

from algaeapp import algaeApp
from algaedb import algaeDB
from algaetblreferencebase import algaeTblReferenceBase

class refDataType(algaeTblReferenceBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'ref.data_type'
        self.min_value = None
        self.max_value = None
        self.nodata_value = None
        self.size_bytes = None








