"""

 Slate | Signature and sp.signature support.
 
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

from slateapp import slateApp
from slateplace import slatePlace
from slatelayer import slateLayer

class slateSignature(algaeTblBase):

    def __init__(self):
    # ------------------------------------------------------------------------------
        """
        Constructor.
        """
        super().__init__()
        self.table_name = 'sp.signature'
        self.numeric_val = None
        self.place = slatePlace()
        self.layer = slateLayer()









