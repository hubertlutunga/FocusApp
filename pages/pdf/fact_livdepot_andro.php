	<?php			


	include("../../../pages/bdd.php");
				
      		$sqllogo = "SELECT * from logo ";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

    


      $codcom = $_GET['codcom'];
      $codliv = $_GET['ref'];


//------------livraison---------------

      $sqldl = "SELECT * from com_livraison WHERE cod_cl = '$codliv'";
      $dl = $pdo->query($sqldl);  
      $row_dl = $dl->fetch();


      $sqlv = "SELECT * from abc_user WHERE matr_user = '{$row_dl['agent']}'";
      $reqv = $pdo->query($sqlv);  
      $row_agent = $reqv->fetch();

//-----------------------------------

      $sqlx = "SELECT * from commandes where code_com = '$codcom'";

      $reqx=$pdo->query($sqlx);
      $row_list_com = $reqx->fetch();


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();


      $sqlc = "SELECT * from client_boul WHERE matr_cli = '{$row_list_com['code_cli']}'";
      $reqc = $pdo->query($sqlc);  
      $row_info_client = $reqc->fetch();


      $req="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}' ORDER by cod_rec ASC";
      $pv=$pdo->query($req);
      $row_pv=$pv->fetch();

?>




      <img src="../../images/<?php echo $row_logo['logo']; ?>" width="60%">
		
	<br>

	<p>LIVRAISON N°: <?php echo $codliv; ?> </p>
	<p>Client(e):<br> <b><?php echo $row_info_client['prenom'].' '.$row_info_client['nom']; ?></b> <br>
		Date: <?php echo date('d/m/Y à H:i',strtotime($row_dl['date_liv'])); ?> </p>
	
	<p> Qte Commandé: <b><?php echo $row_dl['qte_com']; ?></b> 
	<br>Qte Réçu: <b><?php echo $row_dl['qte_liv']; ?></b>
	<br>Livreur: <b><?php echo $row_agent['prenom'].' '.$row_agent['nom']; ?></b> 
	<br>Recept.: <b><?php echo $row_dl['recep']; ?></b>
	<br>
	<br><span style="text-decoration: underline;">Signature</span></p>
	

               <script>

var obj = 'window.location.replace("../../index.php?page=mb_livrer_depot_print&cod=<?php echo $codcom; ?>&codcl=<?php echo $codliv; ?>");';
setTimeout(obj,1000);


               </script>


