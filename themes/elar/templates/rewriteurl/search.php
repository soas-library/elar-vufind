<?php

$collection_id = $_GET['id'];

//echo $collection_id;

$completeurl = "http://localhost:8080/solr/biblio/select?q=corpusid%3A".$collection_id."&fl=id&wt=xml&indent=true";
$xml = simplexml_load_string(file_get_contents($completeurl));

//print_r($xml);

//print_r($xml->result["numFound"]);
//echo '<h1>'.$xml->result["numFound"].'</h1>';

//print_r($xml->result->doc->str);
//echo '<h1>'.$xml->result->doc->str.'</h1>';

$numFound = $xml->result["numFound"];

if(strcmp($numFound, "1") === 0)
{
	$mpi_id = $xml->result->doc->str;
	if(!empty($mpi_id)) {
		$url = "http://" . $_SERVER['SERVER_NAME'] . "/Collection/" . $mpi_id;
	} else {
		$url = "http://" . $_SERVER['SERVER_NAME'] . "/Search/Results?lookfor=" . $collection_id . "&type=DepositId";
	}
	
} else {
	$url = "http://" . $_SERVER['SERVER_NAME'] . "/Search/Results?lookfor=" . $collection_id . "&type=DepositId";
}

//echo $url;

header("Location: ". $url);
exit;

?>