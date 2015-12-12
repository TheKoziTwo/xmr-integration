<?php include 'qr/qrlib.php';
(!empty($_GET['id']) ? $_GET['id'] : null);
$id = $_GET['id'];

QRcode::png($id,false,QR_ECLEVEL_L,4,5,false);