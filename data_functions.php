<?php

/*
Open AntumID SOAP Server V1.1 - 2018 - Antum

Created for the Open AntumID Project - 2018 - DigiByte Community - DigiByte AWT - www.antumid.be / www.antum.be

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at  http://www.apache.org/licenses/LICENSE-2.0
Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Requirements:
- PHP DOM extension, for example to install: sudo apt-get install php-dom
- GMP php extension, for example to install: sudo apt-get install php-gmp (Info: https://stackoverflow.com/questions/40010197/how-to-install-gmp-on-php7-and-ubuntu/40010211)
- SOAP Server for example: sudo apt-get install php7.0-soap  and reboot using : sudo systemctl restart apache2 (info: https://stackoverflow.com/questions/2509143/how-do-i-install-soap-extension)
- MySQL Server.
- Create a MySQL database, import struct.sql into it.
- Configure database information and server url in config.php
- Use only SSL on server!!!

*/

$OutOfScopePath = "/var/www/qb104340/data/www/go4ict.be";

require_once dirname(__FILE__) . "/class.Message.php"; // In current directory
require_once($OutOfScopePath . "/digibyte/config.php"); // Outside scope, for security 
require_once($OutOfScopePath . "/digibyte/DigiID.php"); // Outside scope, for security 
require_once($OutOfScopePath . "/digibyte/DAO.php"); // Outside scope, for security 

function sendMessage($message) {	
   $TypeOfRequest = $message->AuthenticationRequestType; // Open AntumID command to use
   $TypeOfRequest = strtolower($TypeOfRequest); // Convert command to lower string values.
   $types = explode("|", $TypeOfRequest); // Get aditional settings using pipe char to devide. Do not use pipe char for your values.
   $TypeOfRequest = $types[0]; // For example: your action.
   $AppOrDomain = $types[1]; // For example: your domain-name or app-name.
   switch ($TypeOfRequest) {       
      case "action_create_auth";  
         return OAID_Server_Action_Create_Auth($message); // Request for Authentication QR always returns JSON.
         break;
      case "action_validate_auth_openantumid";
         return OAID_Server_Action_Validate_Auth($message, $TypeOfRequest, $AppOrDomain); // Return of information based in XML return.
         break;
      default:
         return ReturnXMLResponse("error", "error");
         break;
   }
}

function OAID_Server_Action_Validate_Auth($message, $TypeOfRequest, $AppOrDomain) {
   $MyClientIP = $message->AuthenticationRequestClientIP;
   $MyRecNonce = $message->AuthenticationValidateTokenID; 
   $MyServiceToUse = $message->AuthenticationRequestGUID; // Your KEY that you give to your client (secured key) (Check your internal license model if you want to use this)
   // Use VAR $AppOrDomain for extra domain or app specifications.
   // Create your own ERR-Message Codes here.
   // Add optional check(s) or search.
   $dao = new DAO();
   $result = $dao->address($MyRecNonce, $MyClientIP);    
       if (strlen($result) > 0) {     
         return ReturnXMLResponse_OpenAntumID($TypeOfRequest, "ERR-200", $result);
       } else {
         return ReturnXMLResponse_OpenAntumID($TypeOfRequest, "ERR-400", "");
       }
}

function ReturnXMLResponse_OpenAntumID($TagCommand, $CommandResponse, $DigiAddressToUse) {
   $RootParentName = 'ResponseAntumID';
   $Element_TAG = 'TAG';
   $Element_RESPONSE = 'RESPONSE';
   $Element_DIGIID = 'DIGIID';
   $parent = new DomDocument('1.0'); // Document
   $parent->formatOutput = true;
   $parent->preserveWhiteSpace = FALSE;
   $parent_node = $parent ->createElement($RootParentName); // Create root
   $parent_node->appendChild($parent->createElement($Element_TAG, $TagCommand));
   $parent_node->appendChild($parent->createElement($Element_RESPONSE , $CommandResponse));
   $parent_node->appendChild($parent->createElement($Element_DIGIID , $DigiAddressToUse));
   $parent->appendChild($parent_node);
   return $parent->saveXML();
  }

function OAID_Server_Action_Create_Auth($message) {
   $digiid = new DigiID(); // Create instance of the DigiID class.
   $nonce = $digiid->generateNonce(); // Generate nonce code.
   // Build URI with nonce, nonce is optional, but we pre-calculate it to avoid extracting it later
   $digiid_uri = $digiid->buildURI(SERVER_URL . 'callback.php', $nonce);
   $digiid_QRimage =  $digiid->qrCode($digiid_uri);
   $MyClientIP = $message->AuthenticationRequestClientIP;
   $dao = new DAO();
   $result = $dao->insert($nonce, $MyClientIP);
   if(!$result)  { var_dump($dao); die(); }
   return ReturnJsonResponse($digiid_QRimage, $digiid_uri, $nonce, $MyClientIP);        
}

function ReturnXMLResponse($TagCommand, $CommandResponse) {
 $RootParentName = 'ResponseAntumID';
 $Element_TAG = 'TAG';
 $Element_RESPONSE = 'RESPONSE';
 $parent = new DomDocument('1.0'); // Document
 $parent->formatOutput = true;
 $parent->preserveWhiteSpace = FALSE;
 $parent_node = $parent ->createElement($RootParentName); // Create root
 $parent_node->appendChild($parent->createElement($Element_TAG, $TagCommand));
 $parent_node->appendChild($parent->createElement($Element_RESPONSE , $CommandResponse));
 $parent->appendChild($parent_node);
  return $parent->saveXML();
}

function ReturnJsonResponse($DigiQR, $DigiIDURL, $uncCode, $ClientIpOfRequest) {
   $e = new Emp();
   $e->QRIMAGE = $DigiQR;
   $e->URLCALLBACK  = $DigiIDURL;
   $e->NONCE = $uncCode;
   $e->CLIENTIP = $ClientIpOfRequest;
   return json_encode($e);
}

class Emp {
   public $QRIMAGE= "";
   public $URLCALLBACK  = "";
   public $NONCE = "";
   public $CLIENTIP = "";
}


?>