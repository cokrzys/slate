"""

  slate | Source data and support for sp.source_data.
  
  Source data is a container for one or more source files.

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
from algaetblbase import algaeTblBase
from algaetblrecordstatus import algaeTblRecordStatus

from refdatagroup import refDataGroup
from refdatalocation import refDataLocation
from slateproject import slateProject

class slateSourceData(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.source_data'        
        self.project = slateProject()
        self.name = None
        self.description = None
        self.record_status = algaeTblRecordStatus()
        self.data_group = refDataGroup()
        self.data_location = refDataLocation()
        self.folder = None
        self.url = None








