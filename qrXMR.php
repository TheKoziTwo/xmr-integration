<?php include 'qr/qrlib.php';
(!empty($_GET['id']) ? $_GET['id'] : null);
$id = $_GET['id'];

QRcode::png($id,false,M,4,5,false);