<?php
session_start();

$ci = curl_init();
 
curl_setopt( $ci, CURLOPT_URL, "http://localhost/www/API/produtos/all" ); 
// curl_setopt( $ci, CURLOPT_PUT, true);
// curl_setopt( $ci, CURLOPT_POSTFIELDS, array(
//     'email' => 'cat_boris@hotmail.com',
//     'senha' => 'minha_senha_cadastrada'
// ));
curl_setopt( $ci, CURLOPT_HEADER, false );
curl_setopt( $ci, CURLOPT_RETURNTRANSFER, 1 );
 
$result = curl_exec( $ci );
print_r($result);