<?php
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$rankAlgorithm = isset($_GET['rankAlgorithm']) ? $_GET['rankAlgorithm'] : false;
$results = false;
$lucenePagerankParameters = array(
  'fl' => 'title,og_url,og_description,id'
);
$networkxPagerankParameters = array(
  'fl' => 'title,og_url,og_description,id',
  'sort' => 'pageRankFile desc'
);


if ($query)
{
  require_once('Apache/Solr/Service.php');
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  try
  {
    if ($rankAlgorithm != "lucene") {
      $results = $solr->search($query, 0, $limit, $networkxPagerankParameters);
    } else {
      $results = $solr->search($query, 0, $limit, $lucenePagerankParameters);
    }
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
    <title>HW4</title>
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
      <div style="height: 20px;"></div>
      <form  accept-charset="utf-8" method="get">
        <div class="form-group row">
          <label class="col-form-label" for="q" style=" font-style:bold;font-size: 25px"><span style ="color:blue">S</span><span style ="color:red">e</span><span style ="color:orange">a</span><span style ="color:green">r</span><span style ="color:purple">c</span><span style ="color:black">h</span></label>
          <div class="col-xl-4">
            <input class="form-control" id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
          </div>
          <div class="col-xl-6">
            <input name="rankAlgorithm" class="btn btn-outline-secondary" type="submit" <?php if($rankAlgorithm != "networkx") { echo "checked='checked'"; } ?> value="lucene" /> 
            <input name="rankAlgorithm" class="btn btn-outline-secondary" type="submit" <?php if($rankAlgorithm == "networkx") { echo "checked='checked'"; } ?> value="networkx" />
          </div>
        </div>
      </form>
<?php

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
  foreach ($results->response->docs as $doc)
  {
?>
          <li>
            <table style="border: 0px solid black; text-align: left;">
 
          <?php
            $id = $doc->id;
            if (isset($doc->og_url)) {
              $url = $doc->og_url;
            }
          ?>
              <!-- title -->
              <tr>
                <td>
                  <a target="_blank" href="<?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>">
                    <span id="title" style = "font-size: 16px; color:blue"><?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?></span>
                  </a>
                </td>
              </tr>
              <!-- url -->
              <tr><td><a target="_blank" href="<?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8'); ?>"><span id="url" style = "font-size: 12px;color:green">
                  <?php echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8');?></span></a></td></tr>
              <!-- description -->
              <tr>
                <td>
                  <span id="description" style = "font-size: 14px">
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
              <!-- id -->
              <tr><td><span id="id" style = "font-size: 14px;color:grey"><?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');?></span></td></tr>
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
</html>