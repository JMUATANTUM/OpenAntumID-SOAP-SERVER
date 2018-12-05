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

// Include(s)
$OutOfScopePath = "/var/www/qb104340/data/www/go4ict.be";
require_once($OutOfScopePath . '/dgb-services/api/v1/functions/class.Message.php');
require_once($OutOfScopePath . '/dgb-services/api/v1/functions/data_functions.php');

// SOAP SERVER REQUEST - STARTED
$server = new SoapServer($OutOfScopePath . "/dgb-services/api/v1/messageService.wsdl", array('cache_wsdl' => WSDL_CACHE_NONE)); // Disable cache
$server->addFunction("sendMessage");
$server->handle();
?>
