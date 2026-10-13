<?php

/**

  slate | Folder report(s).
  
  Initially setup to show a report of files in a folder with associated actions for
  known file formats.
  
  @author    Brian Krzys (brian.krzys@rtspatial.com)
  @copyright (c) 2026 RTSpatial Ltd.
  @license   SPDX-License-Identifier: MIT
  @link      https://github.com/cokrzys/slate

*/

class slateFolder
{
  
  /**
   * Constructor.
   */
  public function __construct()
  // --------------------------------------------------------------------------
  {
  }
  
  /**
   * Report files for the process.
   */
  public static function reportFiles($folder)
  // --------------------------------------------------------------------------
  {
    if (file_exists($folder))
    {
      $files = scandir($folder);
      if (sizeof($files) > 2)
      {
        echo sizeof($files), ' file(s) in ', $folder, '<p />';
        //
        // ----- initial the table
        //
        $tableId = 'filesTable';
        algaeTable::initTablesorterJavascript($tableId, '[[0,0]]', True, "headers: {2: {sorter:'milDate'} }");
        algaeTable::start($tableId, 'tablesorter', 'width:80%;');
        //
        // ----- table header
        //
        $header_array = array(
          array('Filename', '40%'),
          array('Size (bytes)', '20%'),
          array('Date', '20%'),
          array('Actions', '20%')
        );
        algaeTable::writeHeader($header_array, True);
        //
        // ----- loop through the results
        //
        foreach ($files as $file)
        {
          if ( ($file != '.') && ($file != '..') )
          {
            $full_filename = $folder . $file;
            echo '<tr>';
            algaeTable::writeData($file);
            algaeTable::writeData(algaeCore::getFormattedNumber(filesize($full_filename), 0));
            algaeTable::writeData(date("d-M-Y G:i:s", filemtime($full_filename)), 0);
            algaeTable::writeData('-');
            // algaeTable::writeData($this->getFileActions($full_filename), False);
            echo '</tr>';
          }
        }
        algaeTable::end();
      }
      else
      {
        echo 'No file(s) in ', $folder, '<p />';
      }
    }
    else
    {
      echo 'Folder ', $folder, ' does not exist.<p />';
    }
  }
    
}






