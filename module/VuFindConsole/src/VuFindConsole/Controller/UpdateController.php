<?php
/**
 * CLI Controller Module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_a_controller Wiki
 */
namespace VuFindConsole\Controller;
use Zend\Console\Console,

PDO, PDOException, VuFind\Exception\ILS as ILSException;
/**
 * This controller handles various command-line tools
 *
 * @category VuFind2
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:building_a_controller Wiki
 */
class UpdateController extends AbstractBase
{
	
    public function updatenewAction()
    {

        $urlSolr = 'http://localhost:8080/solr/biblio/select?q=*%3A*+NOT+deletion_message%3Adeleted&&rows=9999999&fl=id+hierarchy_top_id&wt=xml&indent=true';
        //$urlSolr = 'http://localhost:8080/solr/biblio/select?q=*%3A*+NOT+deletion_message%3Adeleted&&rows=9&fl=id+hierarchy_top_id&wt=xml&indent=true';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$urlSolr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        $allList = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($allList, 'SimpleXMLElement', LIBXML_NOCDATA);
	
	$vufindAll = array();

	
	$listCount=0;
     	foreach( $xml->result->doc  as $result ) {
		foreach( $result->str as $str ) {
			if($str['name'] == 'id') {
				$listId= $str."";
				$vufindAll[] = str_replace("MPI","",$listId);
			}
		}
		foreach( $result->arr as $arr ){
			if($arr['name'] == 'hierarchy_top_id') {
				foreach($arr as $arr_item){
					$depositId= $arr_item."";
					$vufindAllDeposit[] = str_replace("MPI","",$depositId);
				}
                	}
                }
                //echo "id".$listId . "deposit".$depositId."\n";
        }


	$harvestAll = array();
        $dir = "/usr/local/vufind/local/harvest/ELARALL/";
        $handle = opendir($dir);
        while ($file = readdir($handle)) {
            if (is_file($dir.$file)) {
            	$id=strrpos($file, "_");
            	$final = str_replace("MPI","",str_replace(".xml","",substr($file,$id+1,9999)));
            	$harvestAll[]=$final;
            }
        }

	$diferences=array_diff($vufindAll,$harvestAll);

	$texto =  "VUFIND DOCUMENTS " . count($vufindAll);
	$texto = $texto ."\n";
	$texto = $texto ."OAI DOCUMENTS " . count($harvestAll);
	$texto = $texto ."\n";
	$texto = $texto ."DIFFERENCES " . count($diferences);
	$textoFinal = $texto . "\n";

	echo $textoFinal;

        #sb174 2018-04-23
        #This if statement was built in as a safeguard mechanism to prevent VuFind accidentally deleting more than 200 records when the LAT OAI-PMH provider fails. In cases where bulk deletions from Elar are required, this line should be edited to put a higher number in place of 200.
	#if (count($diferences) < 3000){
	if (count($diferences) < 500){
		foreach($diferences as $item){
			echo "posicion " . array_search($item, $vufindAll)."\n";
			$laPos= array_search($item, $vufindAll);
			echo "deposito" . $vufindAllDeposit[$laPos];
			$depositId=$vufindAllDeposit[$laPos];
			$linea = "http://localhost:8080/solr/biblio/update?stream.body=<add><doc><field name=\"id\">MPI".$item."</field><field name=\"deletion_message\">deleted</field><field name=\"hierarchy_top_id\">MPI".$depositId."</field></doc></add>&commit=true";
			$linea2=str_replace(" ","%20",$linea);
			$linea2=str_replace("'","%27",$linea2);
			$linea2=str_replace("\"","%22",$linea2);
			echo "DOCUMENT DELETED ". $item. "\n";
			$texto = $texto ."DOCUMENT DELETED ". $item. "\n";
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL,$linea2);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
		        $allList = curl_exec($ch);
		        curl_close($ch);
		}
	}
	else {
			$texto = $texto ."Documents not deleted due to be more than 200 (".count($diferences). ")\n";
			$texto = $texto ."Please contact the technical staff.". "\n";
	}
    $mensaje = wordwrap($texto, 70, "\r\n");
    
    $fichero = '/usr/local/vufind/update/listado.txt';
    $gestor = fopen($fichero, "c+");
    $contenido="";
    foreach($diferences as $item){
       if($item!="9999") $contenido = $contenido . "\n" . "MPI".$item;
    }
    $actual = file_put_contents($fichero,$contenido);

    $archivo = file_get_contents($fichero);
    $archivo = chunk_split(base64_encode($archivo));

    $headers = "From: ELAR Deleted documents <" . "wurin@soas.ac.uk" . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";

    $email_message = "--=A=G=R=O=\r\n";
    $email_message .= "Content-type:text/plain; charset=utf-8\r\n";
    $email_message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $email_message .= $mensaje . "\r\n\r\n";
    $email_message .= "--=A=G=R=O=\r\n";
    $email_message .= "Content-Type: application/octet-stream; name=\"" . $fichero . "\"\r\n";
    $email_message .= "Content-Transfer-Encoding: base64\r\n";
    $email_message .= "Content-Disposition: attachment; filename=\"" . $fichero . "\"\r\n\r\n";
    $email_message .= $archivo . "\r\n\r\n";
    $email_message .= "--=A=G=R=O=--";

    $mensaje = $email_message;
    
    //mail('xabimolero@gmail.com', 'ELAR Deletion process', $mensaje, $headers);
    mail('sb174@soas.ac.uk', 'ELAR Deletion process', $mensaje, $headers);

    mail('csbs@soas.ac.uk', 'ELAR Deletion process', $mensaje, $headers);

    mail('elararchive@soas.ac.uk', 'ELAR Deletion process', $mensaje, $headers);
    }
}
