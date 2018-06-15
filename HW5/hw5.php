<?php
ini_set('memory_limit','1024M');
// make sure browsers see this page as utf-8 encoded HTML
include 'SpellCorrector.php';
include 'simple_html_dom.php';
header('Content-Type: text/html; charset=utf-8');
$div=false;
$correct = "";
$correct1="";
$output = "";
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  $choice = isset($_REQUEST['sort'])? $_REQUEST['sort'] : "default";
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
  if( ! $solr->ping()) { 
            echo 'Solr service is not available'; 
  } 
  else{
     
  }
  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  try
  {
    if($choice == "default")
      $additionalParameters=array(
      	'fl' => 'title,og_url,og_description,id',
      	'sort' => ''
      );
    else{
      $additionalParameters=array(
      	'fl' => 'title,og_url,og_description,id',
      	'sort' => 'pageRankFile desc'
      );
    }
    $word = explode(" ",$query);
    $spell = $word[sizeof($word)-1];
    for($i=0;$i<sizeOf($word);$i++){
      ini_set('memory_limit',-1);
      ini_set('max_execution_time', 300);
      $che = SpellCorrector::correct($word[$i]);
      if($correct!="")
        $correct = $correct."+".trim($che);
      else{
        $correct = trim($che);
      }
        $correct1 = $correct1." ".trim($che);
    }
    $correct1 = str_replace("+"," ",$correct);
    $div=false;
    if(strtolower($query)==strtolower($correct1)){
      $results = $solr->search($query, 0, $limit, $additionalParameters);
    }
    else {
      $div =true;
      $results = $solr->search($query, 0, $limit, $additionalParameters);
      $link = "http://localhost/~zejunliu/hw5.php?q=$correct&sort=$choice";
      $output = "Did you mean: <a href='$link'>$correct1</a>";
    }
    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted  by searching (i.e. connection
    // problems or a query parsing error)
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>HW5</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  </head>
  <body>
  	<div class="container">
	  <div style="height: 20px;"></div>	
	    <form  accept-charset="utf-8" method="get">
	      <div class="form-group row">
	      
	      
	      <div class="col-xl-7">
          <label class="col-form-label" for="q" style=" font-style:bold;font-size: 25px"><span style ="color:blue">S</span><span style ="color:red">e</span><span style ="color:orange">a</span><span style ="color:green">r</span><span style ="color:purple">c</span><span style ="color:black">h</span></label>
	      	<input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>

		      <input type="radio" name="sort" value="pagerank" class="btn btn-outline-secondary" <?php if(isset($_REQUEST['sort']) && $choice == "pagerank") { echo 'checked="checked"';} ?>>PageRank
		      <input type="radio" name="sort" value="default" class="btn btn-outline-secondary" <?php if(isset($_REQUEST['sort']) && $choice == "default") { echo 'checked="checked"';} ?>>Default
		      <input class="btn btn-outline-secondary" type="submit"/>
		   </div>
	      </div>
	    </form>
 <script>
   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;
     var tags = [];
     $("#q").autocomplete({
       source : function(request, response) {
         var correct="";
         var query = $("#q").val().toLowerCase();
         var space =  query.lastIndexOf(' ');
         if(query.length-1>space && space!=-1){
          correct=query.substr(space+1);
        }
        else{
          correct=query.substr(0); 
        }
        var URL = URL_PREFIX + correct+ URL_SUFFIX;
        $.ajax({
         url : URL,
         success : function(data) {
          var js =data.suggest.suggest;
          var docs = JSON.stringify(js);
          var jsonData = JSON.parse(docs);
          var suggestions =jsonData[correct].suggestions;
          suggestions=$.map(suggestions,function(value,index){
              var prefix="";
              var query=$("#q").val();
              var queries=query.split(" ");
              if(queries.length>1) {
                var lastIndex=query.lastIndexOf(" ");
                prefix=query.substring(0,lastIndex+1).toLowerCase();
              }
              if (prefix == "" && is_stop_word(value.term)) {
                return null;
              }
               if(!/^[0-9a-zA-Z]+$/.test(value.term)) {
                return null;
              }
              return prefix+value.term;
            });
            response(suggestions.slice(0,5));
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
function is_stop_word(stopword) {
  var regex=new RegExp("\\b"+stopword+"\\b","i");
  return stopWords.search(regex) < 0 ? false : true;
 }
 </script>

<?php
if ($div){
  echo $output;
}
$csvArray =  array_map('str_getcsv', file('UrlToHtml_foxnews.csv'));
$count =0;
$pre="";
// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>   
    
    <div style ="color:grey; font-size: 12px">Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <div style="height:10px;"></div>
    <div class="row">
      <div class="col-xl-9">
        <ol class="list-group" style="list-style-type:none;">	
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc){
    $id = $doc->id;
    $title = $doc->title;
    $id = str_replace("/Users/zejunliu/Desktop/solr-7.3.0/crawl_data/","",$id);
    $title = $doc->title;
    foreach ($csvArray as $key ) {
      if ($id == $key[0]){
        $link = $key[1];
        break;
      }
    }
    $searchterm = $_GET["q"];//search content
    $html = file_get_contents("/Users/zejunliu/Desktop/solr-7.3.0/crawl_data/".$id);
    $sentences = explode(".", $html);
    $words = explode(" ", $query);
    $snippet = "";
    $text = "/";
    foreach($words as $item){
      $text=$text."(?=.*?\b".$item."\b)";
    }
    $text=$text."^.*$/i";
    foreach($sentences as $sentence){
      $sentence=strip_tags($sentence);
      if (preg_match($text, $sentence)>0){
        if (preg_match("(&gt|&lt|\/|{|}|[|]|\|\%|>|<|:)",$sentence)>0){
          continue;
        }
        else{
          $snippet = $snippet.$sentence;
          if(strlen($snippet)>156){          
            break;
          } 
        }
      }
    }
    if($snippet == ""){
      $snippet = "N/A";
    }
  //check
?>
      <li>
        <table style="border: 0px solid black; text-align: left;">
          <!-- title -->
          <tr>
            <td>
                <a target="_blank" style="text-decoration:none" href="<?php echo htmlspecialchars($link, ENT_NOQUOTES, 'utf-8'); ?>">
                    <span id="title" style = "font-size: 18px; color:blue"><?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?></span>
                </a>
            </td>
          </tr>
          <!-- description -->
          <tr>
          	<td>
          	   <span id="description" style = "font-size: 12px;color:grey">
                    <?php
                      if (isset($doc->og_description)) {
                        echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8');
                      } else {
                        echo "NA";
                      }
                    ?>
               </span>
            </td>
          </tr>
          <!-- url -->
          <tr><td><a target="_blank" style="text-decoration:none" href="<?php echo htmlspecialchars($link, ENT_NOQUOTES, 'utf-8'); ?>"><span id="url" style = "font-size: 12px;color:green">
                  <?php echo htmlspecialchars($link, ENT_NOQUOTES, 'utf-8');?></span></a></td></tr>
          <!--snippet-->
          <tr>
           <td style = "font-size: 15px"><?php 
            if($snippet == "N/A"){
              echo htmlspecialchars($snippet, ENT_NOQUOTES, 'utf-8');
            }else{
              $snippet = preg_replace("/\w*?$searchterm\w*/i", "<b>$0</b>", $snippet);
              echo "...".$snippet."...";
            }
            ?></td>
          </tr>
          
          <tr><td><span id="id" style = "font-size: 12px;color:grey"><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');?></span></td></tr>
        </table>
      </li>
      <div style="height:20px;"></div>
      
<?php
  }
?>
    </ol>
	</div>
    <div class="col-xl-3"></div>
    </div>
<?php
}
?>
   </div>
  </body>
<script>
var stopWords = "a,able,about,above,across,after,all,almost,also,am,among,can,an,and,any,are,as,at,be,because,been,but,by,cannot,could,dear,did,do,does,either,else,ever,every,for,from,get,got,had,has,have,he,her,hers,him,his,how,however,i,if,in,into,is,it,its,just,least,let,like,likely,may,me,might,most,must,my,neither,no,nor,not,of,off,often,on,only,or,other,our,own,rather,said,say,says,she,should,since,so,some,than,that,the,their,them,then,there,these,they,this,tis,to,too,twas,us,wants,was,we,were,what,when,where,which,while,who,whom,why,will,with,would,yet,you,your,not";
</script>
</html>