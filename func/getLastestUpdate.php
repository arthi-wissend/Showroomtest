<?php
require("globalFunctions.php");

// print timestap of most recent modified file
echo (getLatestUpdate(dirname(__FILE__, 2)));
?>