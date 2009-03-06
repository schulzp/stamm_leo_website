<?php
$current = count($Tags);
$Tags[$current][0] = '<!-- PLUGIN:TIME_USER time -->';
$Tags[$current][1] = date ( "H:i" , time () );
$Tags[$current+1][0] = '<!-- PLUGIN:TIME_USER ip -->';
$Tags[$current+1][1] = $REMOTE_ADDR;
?>