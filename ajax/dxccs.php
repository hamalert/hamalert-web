<?php

session_cache_limiter(false);
require_once("../db.inc.php");

addCacheHeader(time(), 86400);

$dxccs = getAllDxccs();

echo json_encode($dxccs);
