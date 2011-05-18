<?php

// $Id$

class IslandoraBookviewer {

  function __construct($pid = '') {
    module_load_include('inc', 'fedora_repository', 'api/fedora_item');
    module_load_include('inc', 'fedora_repository', 'CollectionClass');
    if (!empty($pid)) {
      $this->pid = $pid;
      $this->item = new Fedora_Item($pid);
    }
  }

  function showBookViewer() {
    global $base_url;
    $path = drupal_get_path('module', 'islandora_bookviewer');
    $fullPath = $path . '/';
    //currently we are loading this as a iframe.
    //can be embeded but it looks terrible when embedde.  the css would have to be rewritten as most things are set as abosolute
    //this will take some time but eventually would be worthwhile as the index php does not have access to drupal functions/variables when loaded as 
    //an iframe
    $viewer_url = $base_url . '/' . $fullPath . 'plugins/BookReaderDemo/index.php?pid=' . $this->pid . '&label=' . $this->item->objectProfile->objLabel;
    $html = '<iframe src="' . $viewer_url . '" scrolling="0" frameborder="0" style="width: 100%; height: 800px;">Errors: unable to load viewer</iframe>';
    return $html;
  }
  
 /**
   * @author: William Panting
   * 
   * @date: 16/05/2011
   * 
   * @return tabs: the html to display, in the form of a set of tabs
   */
  function showBookStreams()
  {
     //get the book viewer iframe 
    $html=$this->showBookViewer();
    
    //set up tabs
    module_load_include('inc', 'fedora_repository', 'api/fedora_item');
    if (!empty($pid)) {

      $this->pid = $pid;
      $this->item = new Fedora_Item($pid);
    }
    $tabs = array();
    $tabs = array('#type' => 'tabset');
    //note the fedora item
    
    $item = new Fedora_Item($this->pid);
   
    //populate tabs
    $tabs['Description'] = array(
      '#type' => 'tabpage',
      '#title' => 'Description',
      '#content' => $item->get_datastream_dissemination('MODS')
    );
        
    $tabs['View'] = array(
      '#type' => 'tabpage',
      '#title' => 'View',
      '#content' => $html
    );
    //return the tabs for display
    return $tabs;
  }

}

?>