"""

  slate | Source file and support for sp.source_file.
  
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

from reffileformat import refFileFormat
from slatesourcedata import slateSourceData

class slateSourceFile(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.source_file'        
        self.source_data = slateSourceData()
        self.filename = None
        self.description = None
        self.size_bytes = None
        self.file_format = refFileFormat()








