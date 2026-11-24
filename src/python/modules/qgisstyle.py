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
    
    def read_styles(filename):
    #------------------------------------------------------------------------------
        """
        """
        style_list = []
        #
        # ----- read style file
        #
        tree = ET.parse(filename)
        root = tree.getroot()
        
        #
        # ----- read, parse, and save categories (symbols) from the style file
        #
        categories = root.find('renderer-v2').find('categories')
        for category in categories.findall('category'):
            style = qgisstyle.QgisStyle()
            style.numeric_code = int(category.get('symbol'))
            style.character_code = category.get('value')
            style.description = category.get('label')
            if args.verbose: print(u"style.description = %s" % style.description)
            style_list.append(style)
        print("%r categorie(s) parsed from the style file." % len(style_list))
        
        #
        # ----- read, parse, and add the color to each style
        #
        symbols = root.find('renderer-v2').find('symbols')
        num_symbols = 0
        for symbol in symbols.findall('symbol'):
            number = int(symbol.get('name'))
            # print("Found symbol color for name %r" % number
            layer = symbol.find('layer')
            for prop in layer.findall('prop'):
                name = prop.get('k')
                value = prop.get('v')
                parts = value.split(',')
                if name == 'color':
                    if len(parts) == 4 and number < len(style_list):
                        style_list[number].red = int(parts[0])
                        style_list[number].green = int(parts[1])
                        style_list[number].blue = int(parts[2])
                        style_list[number].alpha = int(parts[3])
            num_symbols += 1
        print("%r symbol(s) parsed from the style file." % num_symbols)
        
        contains_zero_category = False
        for style in style_list:
            if style.numeric_code == 0: contains_zero_category = True
        if contains_zero_category:
            for style in style_list:
                style.numeric_code += 1
                
    def create_legend(filename, codes_list, styles_list):
    #------------------------------------------------------------------------------
        """
        Create a JSON legend file.
        """
        legend = {}
        legend['legend'] = []
        #
        # ----- default style to use when no matches in a style file, or there is not style file
        #
        default_style = qgisstyle.QgisStyle()
        default_style.red = 1
        default_style.green = 1
        default_style.blue = 1
        default_style.alpha = 1
        #
        # ----- open legend file to write to
        #
        leg = open(filename, 'w')
        if leg != None:
            for code in codes_list:
                pixel_value = unique.index(code) + 1
                #
                # ----- create legend from matches in the style file
                #
                if len(styles_list) > 0:
                    style_index = qgisstyle.QgisStyle.find_style_in_list(style_list, code)
                    if style_index is not None:
                        styles_list[style_index].numeric_code = pixel_value
                        legend['legend'].append(styles_list[style_index].get_dictionary())
                    else:
                        default_style.character_code = code
                        default_style.numeric_code = pixel_value
                        legend['legend'].append(default_style.get_dictionary())
                else:
                    #
                    # ----- no style file just assign a random color
                    #
                    default_style.character_code = code
                    default_style.numeric_code = pixel_value
                    default_style.create_random_color()
                    legend['legend'].append(default_style.get_dictionary())
                    
            json.dump(legend, leg, indent=2)              
            leg = None
                
        else:
            print('ERROR: Unable to create the legend file ' + filename + '.')