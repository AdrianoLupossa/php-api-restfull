<?php
echo "<title>API ERROR 405: METHOD NOT ALLOWED</title>";
echo "<h1>Método não permitido!</h1>";
echo "<p>O método GET não é permitido para o URL pedido.</p>";
echo "<p>If you think this is a server error, please contact the <a href=\"mailto:adrianolupossa@gmail.com\">webmaster</a>.</p>";
echo "<h2>Error 405</h2>";
http_response_code(405);
