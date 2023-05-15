<?php
require_once __DIR__ . '/../vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
$dompdf = new Dompdf();

$style = '
<style>
	body {font-family: "Dejavu sans"}
	h1 {font-size:18px;text-align:center;}
	table {border-collapse:collapse;font-size:14px;padding-top:15px;}
	table th {border:1px solid #ccc;padding: 5px 10px;background: #eee;}
	table td {border:1px solid #ccc;padding: 5px 10px;text-align:center;}
	table.member_detail_info tr:nth-child(odd) {background-color: #e2eaed;}
	table.member_detail_info tr td[colspan] {font-weight: bold;background-color: #83e2c2;}
</style>';
$dompdf->loadHtml($style . '<h1>' . $_POST['header'] . '</h1>' . $_POST['table']);
$dompdf->setPaper('A4');
$dompdf->render();
$dompdf->stream();