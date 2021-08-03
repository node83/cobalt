<?php
declare(strict_types=1);

if ((PHP_SAPI === 'cli-server') && preg_match('`\.(css|gif|ico|jpeg|jpg|js|map|png)$`i',
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require __DIR__ . '/public/index.php';
