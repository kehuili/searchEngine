<?php
//$root = $_SERVER['DOCUMENT_ROOT'];
include('SpellCorrector.php');
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
function getMatch($regex,$reg1,$str){
	$snpt = "";
	if(preg_match($regex,$str,$matches)){
		$snpt = $matches[0];
//echo $regex." ".$snpt;
		//$snpts = explode("/.!?,;: /",$snpt);
		if($snpt[strlen($snpt)-1]=="."){
			
		}else{$snpt=$snpt.".";}
		if(strlen($snpt)>170){
			$start = 0;
			$len = 160;
			for($i=$len;$i+$start<strlen($snpt);$i++){
				if($snpt[$start+$i]==" "){
					$len=$i;
					break;
				}	
			}
			$save = $snpt;
			$snpt = substr($snpt, $start, $len+1);
			$snpt.="...";
			$len=160;
			if(preg_match($reg1,$snpt,$matches,PREG_OFFSET_CAPTURE)==0){
				if(preg_match($reg1,$save,$matches,PREG_OFFSET_CAPTURE)!=0){
					$start = $matches[0][1];
					for($i=$len;$i+$start<strlen($save);$i++){
						if($snpt[$start+$i]==" "){
							$len=$i;
							break;
						}	
					}
				}
				$snpt = substr($save, $start, $len+1);
				if(strlen($snpt)<160){
					
				}else{$snpt.="...";}
			}
		}
	}
	$snpt = preg_replace($reg1,"<b>$0</b>", $snpt);
	return $snpt;
}
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : false;
$results = false;
$additionalParameters = array(
	'sort' => 'pageRankFile desc',
);

if ($query)
{
$pieces = explode(" ",$query);
	if($check==false){
		$flag = false;
		$correctedQuery="";
$corrected = SpellCorrector::correct($query);
			$correctedQuery .= $corrected;
if($corrected!=strtolower($query)){
				$flag = true;
			}
		/*foreach($pieces as $piece){
			$piece = strtolower($piece);
			$corrected = SpellCorrector::correct($piece);
			$correctedQuery .= $corrected;
			$correctedQuery .= " ";
		
			if($corrected!=$piece){
				$flag = true;
			}
		}*/
	}else{
		$correctedQuery=$query;
	}
	$correctedQuery=trim($correctedQuery);
	
	
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $correctedQuery = stripslashes($correctedQuery);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
	if($_GET['action']=='PageRank'){
    	$results = $solr->search($correctedQuery, 0, $limit,$additionalParameters);
	}else{
    	$results = $solr->search($correctedQuery, 0, $limit);
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
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

    <title>PHP Solr Client Example</title>
		<script>
			$(function() {
				$( "#q" ).autocomplete({
				  source: "suggest.php",
				  minLength: 1,
					focus: function( event, ui ) {
								var temp = $("#q").val().trim();
								var strs = temp.split(" ");
								temp="";
								for(i = 0;i<strs.length-1;i++){
									temp+=strs[i]+" ";
								}
								$( "#q" ).val( temp+ui.item.term );
								return false;
					},
				  select: function( event, ui ) {
				    var temp = $("#q").val().trim();
								var strs = temp.split(" ");
								temp="";
								for(i = 0;i<strs.length-1;i++){
									temp+=strs[i]+" ";
								}
								$( "#q" ).val( temp+ui.item.term );
								return false;
					}
				})
				.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
								var temp = $("#q").val().trim();
								var strs = temp.split(" ");
								temp="";
								for(i = 0;i<strs.length-1;i++){
									temp+=strs[i]+" ";
								}
				  return $( "<li>" )
				    .append( "<a>" + temp+item.term + "</a>" )
				    .appendTo( ul );
				};
			});
		</script>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit" name="action" value="Submit"/>
      <input type="submit" name="action" value="PageRank"/>
    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
	if($flag){
?>
	<div style="font-size:19px">Showing results for <i>
	<a href="http://localhost/solr-php-client/solrQuery.php?q=<?php echo $correctedQuery?>&action=Submit"><?php echo $correctedQuery; ?></i>
	</a></div>
	<div style="font-size:15px">Search instead for <i>
	<a href="http://localhost/solr-php-client/solrQuery.php?q=<?php echo $query?>&action=Submit&check=true"><?php echo $query;?></i></a></div>
	<br />
<?php
}
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <!--table style="border: 1px solid black; text-align: left"-->
<?php
    // iterate document fields / values
	$id="N/A";
	$title="N/A";
	$url="N/A";
	$desc="N/A";
		
    foreach ($doc as $field => $value)
    {
		if($field=="id") {
			$id=htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
			$path = explode("/",$id);
			$path = $path[sizeof($path)-1];
		}elseif($field=="title"){
			$title=htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
		}elseif($field=="og_url"){
			$url=htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
		}elseif($field=="description"){
			$desc=htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
		}
?>
           
<?php
    }
	if($url=="N/A"){
		if (($handle = fopen("NYD Map.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if(preg_match("/$data[0]$/", $id)){
					$url = $data[1];
				}
	  	}
	  		fclose($handle);
		}
	}
?>
        <!--/table-->
						<a href="<?php echo $url ?>"><p style="margin-top:-10px"><b>title:</b> <?php echo $title ?></p></a>
            <a href="<?php echo $url ?>"><h5 style="margin-top:-10px"><b>url:</b> <?php echo $url ?></h5></a>
            <p style="margin-top:-10px"><b>id:</b> <?php echo $id ?></p>
            <p style="margin-top:-10px"><b>description:</b> <?php echo $desc ?></p>
<?php
	$reg = str_replace(" ","|",$correctedQuery);
	$reg1 = str_replace(" ",".*",$correctedQuery);
	$reg2="";
	for($i=0;$i<sizeof($pieces);$i++){
		if($i==sizeof($pieces)-1){$reg2.="\b(reg)\b";}else{
		$reg2.="\b(reg)\b.*";}
	}
	$snippet = "";
	$html = file_get_contents("NYD/".$path);
	$dom = new domDocument('1.0', 'utf-8'); 
	$dom->loadHTML($html); 
	$dom->preserveWhiteSpace = false; 
	$hone= $dom->getElementsByTagName('h1'); 
	$htwo= $dom->getElementsByTagName('h2'); 
	$hthree= $dom->getElementsByTagName('h3'); 
	$article= $dom->getElementsByTagName('article'); 
	$p= $dom->getElementsByTagName('p'); 
	if($snippet==""){
		for($i=0;$i<$p->length;$i++){
			$para = $p->item($i)->nodeValue;
			$snippet = getMatch("/.*$correctedQuery.*/i","/$correctedQuery/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		$article = $article->item(0)->nodeValue;
		$snippet = getMatch("/.*$correctedQuery.*/i","/$correctedQuery/i",$article);
	}
	if($snippet==""){
		for($i=0;$i<$hone->length;$i++){
			$para = $hone->item($i)->nodeValue;
			$snippet = getMatch("/.*$correctedQuery.*/i","/$correctedQuery/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$htwo->length;$i++){
			$para = $htwo->item($i)->nodeValue;
			$snippet = getMatch("/.*$correctedQuery.*/i","/$correctedQuery/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$hthree->length;$i++){
			$para = $hthree->item($i)->nodeValue;
			$snippet = getMatch("/.*$correctedQuery.*/i","/$correctedQuery/i",$para);
			if($snippet!=""){break;}
		}
	}

	if($snippet==""){
		for($i=0;$i<$p->length;$i++){
			$para = $p->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg1)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		$snippet = getMatch("/.*\b($reg1)\b.*/i","/\b($reg)\b/i",$article);
	}
	if($snippet==""){
		for($i=0;$i<$hone->length;$i++){
			$para = $hone->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg1)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$htwo->length;$i++){
			$para = $htwo->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg1)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$hthree->length;$i++){
			$para = $hthree->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg1)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}


	if($snippet==""){
		for($i=0;$i<$p->length;$i++){
			$para = $p->item($i)->nodeValue;
			$snippet = getMatch("/.*$reg2.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		$snippet = getMatch("/.*$reg2.*/i","/\b($reg)\b/i",$article);
	}
	if($snippet==""){
		for($i=0;$i<$hone->length;$i++){
			$para = $hone->item($i)->nodeValue;
			$snippet = getMatch("/.*$reg2.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$htwo->length;$i++){
			$para = $htwo->item($i)->nodeValue;
			$snippet = getMatch("/.*$reg2.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$hthree->length;$i++){
			$para = $hthree->item($i)->nodeValue;
			$snippet = getMatch("/.*$reg2.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}

	if($snippet==""){
		for($i=0;$i<$p->length;$i++){
			$para = $p->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		$snippet = getMatch("/.*\b($reg)\b.*/i","/\b($reg)\b/i",$article);
	}
	if($snippet==""){
		for($i=0;$i<$hone->length;$i++){
			$para = $hone->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$htwo->length;$i++){
			$para = $htwo->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet==""){
		for($i=0;$i<$hthree->length;$i++){
			$para = $hthree->item($i)->nodeValue;
			$snippet = getMatch("/.*\b($reg)\b.*/i","/\b($reg)\b/i",$para);
			if($snippet!=""){break;}
		}
	}
	if($snippet=="") {$snippet="N/A";}

?>
            <p style="margin-top:-10px"><b>snippet:</b> <?php echo $snippet ?></p>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>