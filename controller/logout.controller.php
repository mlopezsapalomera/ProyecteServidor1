<?php
require_once __DIR__ . '/../security/auth.php';

tancarSessio();
header('Location: ../view/index.php?ok=' . urlencode('Sessió tancada.'));
exit;
