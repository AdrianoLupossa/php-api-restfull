<?php
echo "<title>API ERROR 403</title>";
echo "<h1>Forbidden - acesso não permitido!</h1>";
echo "<p>O método GET não é permitido para o URL pedido.</p>";
echo "<p>If you think this is a server error, please contact the <a href=\"mailto:adrianolupossa@gmail.com\">webmaster</a>.</p>";
echo "<h2>Error 403</h2>";
http_response_code(403);
