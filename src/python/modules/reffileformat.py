"""

  slate | Support for file formats and the ref.file_format table.
 
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

from reffilegroup import refFileGroup

class refFileFormat(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'ref.file_format'
        self.record_status = algaeTblRecordStatus()
        self.extension = None
        self.name = None
        self.description = None
        self.file_group = refFileGroup()







