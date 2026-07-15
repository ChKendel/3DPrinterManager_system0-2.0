<?php
	// Lädt das aktuelle Webcam-Standbild eines Druckers und legt es im
	// Nutzerordner ab. Aufruf erfolgt per AJAX aus der (eingeloggten) Übersicht.
	session_start();

	// 1) Nur für angemeldete Nutzer – vorher war der Endpoint anonym erreichbar.
	if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
		http_response_code(403);
		exit;
	}

	// 2) Zielordner ist der des angemeldeten Nutzers (aus der Session, nicht aus
	//    $_GET), damit über den Nutzernamen kein Pfad- oder Befehls-Trick möglich ist.
	$username = $_SESSION["username"];

	// 3) printer_url strikt auf Host[:Port] begrenzen: erlaubt IP/Hostname wie
	//    192.168.3.109 oder octopi1.local:5000, verbietet aber "/" und die
	//    Shell-Metazeichen " ; | ` $ ( ) – damit ist weder eine Command Injection
	//    noch ein Verzeichniswechsel im Dateipfad möglich.
	$printer_url = $_GET["url"] ?? "";
	if (!preg_match('/^[A-Za-z0-9.\-:]+$/', $printer_url)) {
		http_response_code(400);
		exit;
	}

	// Path to save the downloaded image
	$path = "/var/www/html/user_files/$username/$printer_url.jpeg";

	// 4) Snapshot mit PHP-cURL holen statt über die Shell (wget/exec). Ohne
	//    Shell-Aufruf ist eine Command Injection strukturell ausgeschlossen.
	$ch = curl_init("http://$printer_url/webcam/?action=snapshot");
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 10,
		CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS, // nur http/https
	]);
	$image = curl_exec($ch);

	// 5) Nur bei erfolgreichem Abruf speichern.
	if ($image !== false && $image !== "") {
		file_put_contents($path, $image);
	}

?>
