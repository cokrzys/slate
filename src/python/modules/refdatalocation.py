"""

  slate | Where typically source data is stored and the ref.data_location table.
 
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

class refDataLocation(algaeTblReferenceBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'ref.data_location'








