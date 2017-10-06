<?php
/**
 * Fichier copié de grid-excel-php/generate.php, modifié pour afficher les erreurs et ne pas générer de fichier de debug
 * @author Fabrice Pantanella <LOG-IT-MROAD@proximy.fr>
 */

require_once 'gridExcelGenerator.php';
require_once 'gridExcelWrapper.php';

error_reporting(E_ALL);

$debug = false;
$error_handler = set_error_handler("PDFErrorHandler");

if (get_magic_quotes_gpc()) {
    $xmlString = stripslashes($_POST['grid_xml']);
} else {
    $xmlString = $_POST['grid_xml'];
}
$xmlString = urldecode($xmlString);
if ($debug == true) {
    error_log($xmlString, 3, 'debug_' . date("Y_m_d__H_i_s") . '.xml');
}

$xml = simplexml_load_string($xmlString);
$excel = new gridExcelGenerator();
$excel->printGrid($xml);

function PDFErrorHandler($errno, $errstr, $errfile, $errline) {
    global $xmlString;
    if ($errno < 1024) {
        error_log($xmlString, 3, 'error_report_' . date("Y_m_d__H_i_s") . '.xml');
//      exit(1);
    }
}

?>