<?php


      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');

      $cat_source = 'boul';
      $statut = 'liv';
$four = "1";

      $sqlt = "SELECT sum(qte) AS total_n from stock_scr where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_produit = '{$row_list_com['code_prodvente']}' AND code_fourn = '$four'";
      $reqt = $pdo->query($sqlt);  
      $row_nstock = $reqt->fetch();

      $stock_total = $row_nstock['total_n'];




      
      $sqlcom = "SELECT sum(qte) AS total_nc from commandes where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND statut = '$statut' AND agent = '$agent'";
      $reqcom = $pdo->query($sqlcom);  
      $row_ncom = $reqcom->fetch();
      
      $sqlcompv = "SELECT sum(pvt) AS total_nc from commandes where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND statut = '$statut' AND agent = '$agent'";
      $reqcompv = $pdo->query($sqlcompv);  
      $row_ncompv = $reqcompv->fetch();

      $row_ncompvcom = $row_ncompv['total_nc'];

      $sqlpn = "SELECT sum(qte) AS total_nr from panier_scr where YEAR(date_pan) = '$daty' AND Month(date_pan) = '$datm' AND cod_prod = '{$row_list_com['code_prodvente']}'";
      $reqpn = $pdo->query($sqlpn);  
      $row_npn = $reqpn->fetch();



      $total_nc = $row_ncom['total_nc'] + $row_npn['total_nr']; 

      $retour = $stock_total - $total_nc;

      
                              if ($total_nc > 0) {
                                $utiliser_exist_c = $total_nc;
                              }else{

                                $utiliser_exist_c = "0";
                              }

      $utiliser = $utiliser_exist_c;


      $disponible = $stock_total - $utiliser;






                          ?>