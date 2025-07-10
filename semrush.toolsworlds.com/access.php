<?php
include("/var/www/app.toolsworlds.com/library/Am/Lite.php");
Am_Lite::getInstance()->checkAccess(array(2,3,42,43,143,165,44,15,16), 'Semrush');
?>