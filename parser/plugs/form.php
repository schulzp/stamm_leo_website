<?php
// important: ensure that the plugin can't be executed directly:
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

	// Zielverzeichnis einstellen
// dieses Verzeichnis muß eingerichtet sein.
$target_dir = $DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/forms/';

// Name der Zieldatei festlegen
$target_name = $REMOTE_ADDR.'_'.time ();

// Zählt die Tags, die in Verwendung sind
$current = count($Tags);

// Ab phpCMS 1.2.0 existiert das Array $_GET_POST mit dem Inhalt von $_GET und $_POST
// Die 'neuen' Umgebungs-Arrays von PHP (also $_GET, $_POST etc.) existieren in phpCMS nun auch, wenn PHP < 4.2.0 verwendet wird, sind jedoch leider nicht superglobal

// Wenn Get- oder Post-Variablen vorhanden sind:
if(isset($_GET_POST)) {
	$i = 0;
	foreach($_GET_POST as $k => $v) {
		$Tags[($current + $i)][0] = "<!-- PLUGIN:FORM show='".$k."' -->";
		$Tags[($current + $i)][1] = $v;
		$form[$i][0] = $k;
		$form[$i][1] = $v;
		$i++;
	}
}

// Zählt die Tags, die nun in Verwendung sind
$current = count($Tags);

// Fügt noch zwei Tags für die aktuelle Zeit und die IP hinzu
$Tags[$current][0] = "<!-- PLUGIN:FORM show='time' -->";
$Tags[$current][1] = date ( "H:i" , time () );
$Tags[$current + 1][0] = "<!-- PLUGIN:FORM show='ip' -->";
$Tags[$current + 1][1] = $REMOTE_ADDR;

// Werte in Ausgabe Schreiben
$fp = fopen($target_dir.$target_name, "w+");
for($i = 0; $i < count($form); $i++) {
	$entry = $form[$i][0].' = '.$form[$i][1]."\n";
	fputs($fp, $entry, strlen($entry));
}
fclose($fp);

?>