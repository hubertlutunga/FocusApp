<?php 




try
{
  session_start();
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=disiya;charset=utf8", 'root', 'Root_2023');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(Exception $e)

{
        die('Erreur : '.$e->getMessage());
        return false;
}


 /*

try
{
  session_start();
    $pdo = new PDO("mysql:host=localhost;dbname=invizfxg_is;charset=utf8", 'invizfxg_hubert', 'Huberusbb01');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
        return false;
}

*/


error_reporting(E_ALL); ini_set("display_errors", 1);

?>