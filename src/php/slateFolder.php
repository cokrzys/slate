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
  
  protected function isTextFile($filename)
  // --------------------------------------------------------------------------
  {
    $text_extensions = array('log', 'json', 'xml', 'leg', 'prj', 'txt', 'csv', 'gdalinfo');
    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
    foreach ($text_extensions as $ext)
    {
      if (strcasecmp($file_extension, $ext) == 0)
      {
        return True;
      }
    }
    return False;
  }
  
  /**
   * Get menu options for a file.  Actions depend on the file type.
   * @param string $filename Filename to get actions for.
   */
  protected function getFileActions($filename)
  // --------------------------------------------------------------------------
  {
    global $app;
    $html = '';
    $html = algaeFile::getDownloadLink($filename, 'Download', False);
    if ($this->isTextFile($filename))
    {
      $html .= $app->config->menu_separator;
      $page = algaeCore::getFullPath($app->config->algae_root_url, 'view_text_file.php');
      $html .= $app->getPageLink($page . '?filename=' . urlencode($filename), 'View', algaeAccess::ROLE_READ, $app->config->app_name, '', True);
    }
    return $html;
  }
  
  /**
   * Report files for the process.
   */
  public static function reportFiles($folder)
  // --------------------------------------------------------------------------
  {
    $fc = new slateFolder();
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
            $full_filename = algaeCore::getFullPath($folder, $file);
            echo '<tr>';
            algaeTable::writeData($file);
            algaeTable::writeData(algaeCore::getFormattedNumber(filesize($full_filename), 0));
            algaeTable::writeData(date("d-M-Y G:i:s", filemtime($full_filename)), 0);
            algaeTable::writeData($fc->getFileActions($full_filename), False);
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






