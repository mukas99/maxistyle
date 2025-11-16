<?php
/**
 * Simple entry point for the Bitrix24 local REST app.
 * For this specific helper app we don't actually need to do much here —
 * your business-logic lives in parser.php, which you will call from Bitrix24
 * via HTTP-request activity in business processes.
 */

header('Content-Type: text/plain; charset=utf-8');
echo "ID Extractor app is installed and reachable.";
