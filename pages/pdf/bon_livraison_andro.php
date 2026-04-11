	<?php			


	include("../../../pages/bdd.php");
				
      		$sqllogo = "SELECT * from logo ";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

    

 

      $ref = $_GET['ref'];



      $sqlget = "SELECT * from commandes  where num_fact = '$ref'";
      $reqget = $pdo->query($sqlget);  
      $row_list_comget = $reqget->fetch();
            


      $sqlc = "SELECT * from client_boul WHERE matr_cli = '{$row_list_comget['code_cli']}'";
      $reqc = $pdo->query($sqlc);  
      $row_info_client = $reqc->fetch();




      $sql = "SELECT * from abc_user WHERE matr_user = '{$row_list_comget['agent']}'";
      $req = $pdo->query($sql);  
      $row_agent = $req->fetch();

?>



<div style="display: flex;align-items: center;justify-content: center;">
      <img src="../../images/<?php echo $row_logo['logo']; ?>" width="60%">
</div>		
	<br>

	<p style="text-align: center;">BON DE LIVRAISON <br>N°: <?php echo $ref; ?> </p>
      <p style="text-align: center;"><?php echo 'Caisse: '.$row_agent['prenom'].' '.$row_agent['nom'] ?></p>
	<p style="text-align: center;">Client(e):<br> <b><?php echo $row_info_client['prenom'].' '.$row_info_client['nom']; ?></b> 
      <br>
		Date: <?php echo date('Y-m-d à H:i'); ?> </p>



            <?php 


      $sqldpx = "SELECT * from commandes where num_fact = '{$_GET['ref']}' order by code_com ASC";
      $reqdpx = $pdo->query($sqldpx);  
      while ($row_dp = $reqdpx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_dp['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_prod = $reqs->fetch();
                  

             ?>
	
	<p style="text-align: center;"><?php echo '<b>('.$row_dp['qte'].')</b> '.$row_prod['nom_rec']; ?></p>
      <p style="text-align: center;">Qte livrée:............................</p>

	
<?php } ?>

       <p style="text-align: center;border-top: 1px solid #000;"><b>Signature Caisse</b> <br><br><br></p>
      <p style="text-align: center;border-top: 1px solid #000;"><b>Nom et Signature Livreur</b> <br><br><br><br></p>
      <p style="text-align: center;border-top: 1px solid #000;"><b>Nom et Signature Client</b> <br><br><br><br><hr></p> 
               <script>

var obj = 'window.location.replace("../../index.php?page=mb_abc_venteboul_CP");';
setTimeout(obj,1000);


               </script>


