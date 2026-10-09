"""

 slate | A QGIS compatible style.
 
 @author    Brian Krzys (brian.krzys@rtspatial.com)
 @copyright (c) 2026 RTSpatial Ltd.
 @license   SPDX-License-Identifier: MIT
 @link      https://github.com/cokrzys/slate

"""

import sys
import random
import json

class qgisStyle:

    numeric_code = 0
    character_code = ''
    description = ''
    red = 0
    green = 0
    blue = 0
    alpha = 0
    
    def get_dictionary(self):
    #------------------------------------------------------------------------------
        """
        Convert object to a dictionary.
        """
        ret = {}
        ret['numCode'] = self.numeric_code
        ret['charCode'] = self.character_code
        ret['red'] = self.red
        ret['green'] = self.green
        ret['blue'] = self.blue
        ret['description'] = self.description
        return ret
            
    def create_random_color(self):
    #------------------------------------------------------------------------------
        """
        Create a random color.
        """
        self.red = random.randrange(256)
        self.green = random.randrange(256)
        self.blue = random.randrange(256)
        self.alpha = 1

    @staticmethod
    def find_style_in_list(style_list, value):
    #------------------------------------------------------------------------------
        """
        Find a style in a list of styles.
        """
        index = -1
        num = len(style_list)
        cur = 0
        while index == -1 and cur < num:
            if style_list[cur].character_code == value:
                index = cur
            cur += 1
        if index > -1: return index
        return None