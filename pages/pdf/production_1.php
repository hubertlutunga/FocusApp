	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
  //include('../phpqrcode/qrlib.php'); 
	

        


       

	class myPDF extends FPDF{


		
		function infodf($pdo){


                              $req="SELECT * FROM adresse";
                              $ad=$pdo->query($req);
                              $row_ad=$ad->fetch();

                              $req="SELECT * FROM legal";
                              $leg=$pdo->query($req);
                              $row_leg=$leg->fetch();


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,20,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);						
			$this->Cell(195,8,utf8_decode('RD Congo - '.$row_ad['ville']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,utf8_decode('NUMIMPOT: '.$row_leg['num_impot']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,utf8_decode('RCCM: '.$row_leg['rccm']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,utf8_decode('Tél.: '.$row_ad['phone']),0,0,'R');
			$this->Ln(12);			

		
			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);		
			$this->Cell(195,8,utf8_decode('Production N°'.$_GET['codp'].' du '.date('d M Y',strtotime($_GET['da']))),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			

      		$sqllogo = "SELECT * from logo ";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

		
			$this->Image('../../images/'.$row_logo['logo'],8,7,40);

	
      $sqlrp = "SELECT count(*) As total_rp from rapport_production WHERE cod_production = '{$_GET['codp']}'";
      $reqrp = $pdo->query($sqlrp);  
      $row_exist_rapport = $reqrp->fetch();



       if ($row_exist_rapport['total_rp'] < 1) {


 	}else{


			$this->Image('bien_PNG.png',8,200,80);
 	}
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);					
			
		}


		function Infofact($pdo){
			
	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      		
      		$da = $_GET['da'].' 00:00:00';
      		$db = $_GET['da'].' 23:59:59';

	 $statut = 'oui';
	 $raison = "production";

	 $codp = $_GET['codp'];
/*

      $sqlpr = "SELECT * from production WHERE date_production between '$da' and '$db' AND statut = '$statut'  order by code_production DESC";
      $reqlpr = $pdo->query($sqlpr);  
      $row_list_pr = $reqlpr->fetch();

*/








      $sqlistv = "SELECT * from production WHERE date_production between '$da' and '$db' AND statut = '$statut' AND code_production = '$codp'";
      $reqlistv = $pdo->query($sqlistv);  
      while ($row_list_product = $reqlistv->fetch()) {

      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_list_product['cod_rec']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();

      $lign_pro = '('.$row_list_product['qteproduct']. ') '.$row_rec['nom_rec'];

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	
			$this->Cell(185,8,utf8_decode($lign_pro),0,0,'L',true);
			$this->Ln(10);
      			









      $sqlsu = "SELECT * from stock_utiliser where cod_production = '{$row_list_product['code_production']}' AND raison = '$raison' order by cod_su ASC";
      $reqsu = $pdo->query($sqlsu);  
      while ($row_su = $reqsu->fetch()) {


      	$colin = $row_su['cod_ing'];
 		$req="SELECT * FROM ingredien where cod_ing = '$colin'";
 		$ing=$pdo->query($req);
 		$row_ing=$ing->fetch();
  
  
  if (is_numeric($row_su['su'])) {
  	$rowsu = $row_su['su'];
  }else{
  	$rowsu = "Rien";
  }



      $sqlstock_ut = "SELECT * from stock_utiliser where cod_production = '{$row_list_product['code_production']}' AND cod_ing = '$colin'";
      $stock_ut = $pdo->query($sqlstock_ut);  
      $row_stock_ut = $stock_ut->fetch();

      $sqlmp = "SELECT * from marque_produit where cod_mp = '{$row_stock_ut['cod_mp']}'";
      $mp = $pdo->query($sqlmp);  
      $row_momarp = $mp->fetch();


      if (!$row_su['su']) {
      	$rowsu = '0';
      }else{
      	$rowsu = $row_su['su'];
      }
			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

			$this->Cell(100,9,utf8_decode($row_ing['nom_ing'].' '.$row_momarp['nom_mp']),0,0,'L',true);
			$this->Cell(40,9,$rowsu,0,0,'R',true);
			$this->Cell(20,9,utf8_decode($row_ing['unite']),0,0,'L',true);
			$this->Ln(8);

      		
      		}


			$this->Ln(4);
      	}


			$this->Ln(4);


/*
			













      		$da = $_GET['da'].' 00:00:00';
      		$db = $_GET['da'].' 23:59:59';

      $sqlli = "SELECT DISTINCT  cod_ing,cod_production from stock_utiliser WHERE date_su between '$da' AND '$db' AND raison = '$raison' AND cod_production = '$codp'";
      $reqli = $pdo->query($sqlli);  
      while ($row_list_ing = $reqli->fetch()) {


      	$colin = $row_list_ing['cod_ing'];
 		$req_li="SELECT * FROM ingredien where cod_ing = '$colin'";
 		$ing_li=$pdo->query($req_li);
 		$row_ing_li=$ing_li->fetch();

 		$cod_ing = $row_list_ing['cod_ing'];
 		$cod_production = $row_list_ing['cod_production'];

      $sqlto = "SELECT sum(su) AS total_n from stock_utiliser where cod_ing = '$cod_ing' AND cod_production = '$cod_production' AND cod_production = '$codp'";
      $reqto = $pdo->query($sqlto);  
      $row_suto = $reqto->fetch();

      $total_sorti = $row_suto['total_n'];





			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	
			$this->Cell(165,8,utf8_decode($row_ing_li['nom_ing']),0,0,'R',true);
			$this->SetFont('Arial','B',12);
			$this->Cell(20,8,utf8_decode(number_format($total_sorti, 1, '.', ' ').' '.$row_ing_li['unite']),0,0,'L',true);
			
			$this->Ln(7);



		   }






	
      $sqlrp = "SELECT count(*) As total_rp from rapport_production WHERE cod_production = '{$_GET['codp']}'";
      $reqrp = $pdo->query($sqlrp);  
      $row_exist_rapport = $reqrp->fetch();


      $sqlag = "SELECT * from rapport_production WHERE cod_production = '{$_GET['codp']}'";
      $reqag = $pdo->query($sqlag);  
      $row_exist_ag = $reqag->fetch();



      $sqls = "SELECT * from abc_user WHERE identifiant = '{$row_exist_ag['code_agent']}'";
      $reqs = $pdo->query($sqls);  
      $ussessionpro = $reqs->fetch();


      if ($row_exist_rapport['total_rp'] < 1) {

      $agent = 'A produire';

 	}else{


      $agent = 'Produit par '.$ussessionpro['prenom'].' '.$ussessionpro['nom'];

			$this->SetFillColor(255, 255, 255, 0);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	
			$this->Cell(185,8,utf8_decode($agent),0,0,'L',true);
			$this->Ln(10);
 	}



	
*/	









		}
	






		function footer(){
			$this->SetY(-10);
			
			
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,7,7,7,true);
			$this->Cell(0,-3,'Kalipain, Page '.$this->PageNo().'/{nb}',0,0,'R');
			$this->Ln();
			//$this->Image('frame.png',40,3,11);
		}

	}
	















	$pdf=new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('P','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>