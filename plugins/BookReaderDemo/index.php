<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
function do_curl($url, $return_to_variable = 1, $number_of_post_vars = 0, $post = NULL) {
  global $user;
  // Check if we are inside Drupal and there is a valid user.
  if ((!isset ($user)) || $user->uid == 0) {
    $fedora_user = 'anonymous';
    $fedora_pass = 'anonymous';
  }
  else {
    $fedora_user = $user->name;
    $fedora_pass = $user->pass;
  }

  if (function_exists("curl_init")) {
    $ch = curl_init();
    $user_agent = "Mozilla/4.0 pp(compatible; MSIE 5.01; Windows NT 5.0)";
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE); // Fail on errors
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 90); // times out after 90s
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_to_variable); // return into a variable
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, "$fedora_user:$fedora_pass");
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    if ($number_of_post_vars>0&&$post) {
      curl_setopt($ch, CURLOPT_POST, $number_of_post_vars);
      curl_setopt($ch, CURLOPT_POSTFIELDS, "$post");
    }
    return curl_exec($ch);
  }
  else {
    if (function_exists(drupal_set_message)) {
      drupal_set_message(t('No curl support.'), 'error');
    }
    return NULL;
  }
}

function results_to_array($results){
   try {
      $xml = new SimpleXMLElement($results);
    } catch (Exception $e) {
     echo 'error getting book pages';
     exit();
    }
   // var_dump($xml);exit();
    $returnArray = array();
    foreach($xml->results->result as $result){
      $arr = $result->object->attributes();
      $pid = stristr($arr['uri'],'/');
      $returnArray[(string)$result->page]=$pid;
    }
    return $returnArray;
}
//instead of rely on pid or title for ordering we store the page number in
//the rels-ext with a predicate of <info:islandora/islandora-system:def/pageinfo#isPageNumber>
//we then populate an array page numbers as the key and pids as the value
$query_string = 'select $object $page from <#ri>
where $object <fedora-rels-ext:isMemberOf> <info:fedora/'.$_GET["pid"] .'>
and $object <fedora-model:state> <info:fedora/fedora-system:def/model#Active>
and $object <info:islandora/islandora-system:def/pageinfo#isPageNumber> $page';

  $query_string = htmlentities(urlencode($query_string));

    $query_results = '';
    $url = 'http://localhost:8080/fedora/risearch';//have to define the server here we don't have access to drupal variables/functions here
    $url .= "?type=tuples&flush=TRUE&format=Sparql&lang=itql&stream=on&query=" . $query_string;
    $query_results .= do_curl($url);
    $query_array = results_to_array($query_results);

?>

<html>
<head>
    <title>Book Reader</title>
    
    <link rel="stylesheet" type="text/css" href="../BookReader/BookReader.css"></link>
    <!-- Custom CSS overrides -->
    <link rel="stylesheet" type="text/css" href="BookReaderDemo.css"></link>
    
    <script type="text/javascript" src="http://www.archive.org/download/BookReader/lib/jquery-1.2.6.min.js"></script>
    <script type="text/javascript" src="http://www.archive.org/download/BookReader/lib/jquery.easing.1.3.js"></script>
    <script type="text/javascript" src="../BookReader/BookReader.js"></script>    
    <script type="text/javascript" src="../BookReader/dragscrollable.js"></script>
</head>
<body style="background-color: rgb(249, 248, 208);">

<div id="BookReader" style="left:10px; right:10px; top:10px; bottom:2em;">xx</div>
<!--<script type="text/javascript" src="BookReaderJSSimple.js"></script>-->
<script type="text/javascript">
//
// This file shows the minimum you need to provide to BookReader to display a book
//
// Copyright(c)2008-2009 Internet Archive. Software license AGPL version 3.

// Create the BookReader object
var structMap = new Array();
<?php
foreach ($query_array as $key=>$value) {
echo 'structMap['.$key.']= "'.substr($value,1).'";';
}
?>
br = new BookReader();
br.structMap=structMap;
// Return the width of a given page.  Here we assume all images are 800 pixels wide
br.getPageWidth = function(index) {
    return 1600;
}

// Return the height of a given page.  Here we assume all images are 1200 pixels high
br.getPageHeight = function(index) {
    return 2400;
}

// We load the images from archive.org -- you can modify this function to retrieve images
// using a different URL structure
br.getPageURI = function(index, reduce, rotate) {
    // reduce and rotate are ignored in this simple implementation, but we
    // could e.g. look at reduce and load images from a different directory
    // or pass the information to an image server
    var leafStr = br.structMap[index+1];//get the pid of the object from the struct map islandora specific
    var imgStr = (index+1).toString();
    //url below must be changed for now for each install
    var url = 'http://localhost:8080/adore-djatoka/resolver?url_ver=Z39.88-2004&rft_id=http://localhost/drupal/fedora/repository/'+leafStr+'/JP2/outofthinair&svc_id=info:lanl-repo/svc/getRegion&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000&svc.format=image/png&svc.level=5&svc.rotate=0&svc.region=0,0,1600,2400';
    return url;
}

// Return which side, left or right, that a given page should be displayed on
br.getPageSide = function(index) {
    if (0 == (index & 0x1)) {
        return 'R';
    } else {
        return 'L';
    }
}

// This function returns the left and right indices for the user-visible
// spread that contains the given index.  The return values may be
// null if there is no facing page or the index is invalid.
br.getSpreadIndices = function(pindex) {
    var spreadIndices = [null, null];
    if ('rl' == this.pageProgression) {
        // Right to Left
        if (this.getPageSide(pindex) == 'R') {
            spreadIndices[1] = pindex;
            spreadIndices[0] = pindex + 1;
        } else {
            // Given index was LHS
            spreadIndices[0] = pindex;
            spreadIndices[1] = pindex - 1;
        }
    } else {
        // Left to right
        if (this.getPageSide(pindex) == 'L') {
            spreadIndices[0] = pindex;
            spreadIndices[1] = pindex + 1;
        } else {
            // Given index was RHS
            spreadIndices[1] = pindex;
            spreadIndices[0] = pindex - 1;
        }
    }

    return spreadIndices;
}

// For a given "accessible page index" return the page number in the book.
//
// For example, index 5 might correspond to "Page 1" if there is front matter such
// as a title page and table of contents.
br.getPageNum = function(index) {
    return index+1;
}

// Total number of leafs
br.numLeafs = <?php echo count($query_array);?>;

// Book title and the URL used for the book title link
br.bookTitle=  '<?php echo $_GET['label'];?>';
//book url should be created dynamically 
br.bookUrl  = 'http://syn.lib.umanitoba.ca';

// Override the path used to find UI images
br.imagesBaseURL = '../BookReader/images/';

// Let's go!
br.init();



</script>


</body>
</html>
<?php
?>
