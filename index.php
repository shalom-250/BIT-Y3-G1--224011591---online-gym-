<?php
declare(strict_types=1);
// Redirect root request to public directory
header('Location: public/', true, 301);
exit;
