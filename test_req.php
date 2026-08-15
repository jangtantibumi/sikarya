<?php
$content = file_get_contents('http://127.0.0.1:8000/master-portal');
echo strlen($content);
