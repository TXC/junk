<?php

require 'classes/Bitbucket.php';

$API_URL = 'https://api.bitbucket.org/2.0/repositories/basrieter/xbmc-online-tv/downloads';
Bitbucket::factory($API_URL)->run();