<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

$EXPECTED_SIGNATURE = trim(file_get_contents("https://composer.github.io/installer.sig"));
echo "$EXPECTED_SIGNATURE\n";

copy('https://getcomposer.org/installer', 'composer-setup.php');
$ACTUAL_SIGNATURE = hash_file('SHA384', 'composer-setup.php');
echo "$ACTUAL_SIGNATURE\n";

if ($EXPECTED_SIGNATURE !== $ACTUAL_SIGNATURE) {
    echo "ERROR: Invalid installer signature\n";
    unlink("composer-setup.php");
    exit;
}

$RESULT = intval(shell_exec('php composer-setup.php --quiet --install-dir=bin --filename=composer; echo $?'));
echo "$RESULT\n";
unlink("composer-setup.php");
if ($RESULT !== 0) {
    echo "composer-setup failed\n";
    exit;
}

shell_exec('php bin/composer install');
?>
