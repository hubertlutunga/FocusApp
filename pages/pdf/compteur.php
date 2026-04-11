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
			$this->Cell(270,8,utf8_decode('RD Congo - '.$row_ad['ville']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(270,8,utf8_decode('NUMIMPOT: '.$row_leg['num_impot']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(270,8,utf8_decode('RCCM: '.$row_leg['rccm']),0,0,'R');
			$this->Ln(5);			
			$this->Cell(270,8,utf8_decode('Tél.: '.$row_ad['phone']),0,0,'R');
			$this->Ln(12);





			
			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Rapport Compteur de livraison'),0,0,'C');
			$this->Ln(8);	
			$this->Cell(270,8,utf8_decode('('.date('d/m/Y').')'),0,0,'C');
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
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);
			
		}


		function Infofact($pdo){
			
				

      $sqltvaf = "SELECT * from tva ";
      $reqtvaf = $pdo->query($sqltvaf);  
      $row_tvaf = $reqtvaf->fetch();




			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Produits envoyés au livreur'),0,0,'C');
			$this->Ln(8);	 	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(45,8,utf8_decode('#'),0,0,'L',true);
			$this->Cell(126,8,utf8_decode('Produits'),0,0,'L',true);
			$this->Cell(100,8,utf8_decode('Quantité'),0,0,'L',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');


      $q = 1;

      $sqlx = "SELECT * from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND id_depar =  '4' AND recu = 'oui' order by id_st DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_trans = $reqx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_trans['cod_produit']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();
 


			$this->Cell(45,8,utf8_decode($q++),"B",0,'L',true);
			$this->Cell(126,8,utf8_decode($row_nom_prod['nom_rec']),"B",0,'L',true);
			$this->Cell(100,8,utf8_decode($row_trans['qte']),"B",0,'L',true);
			$this->Ln(9);
      		
      		}
      	
      $sqltd = "SELECT sum(qte) AS total_n from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND id_depar =  '4' AND recu = 'oui'";
      $reqtd = $pdo->query($sqltd);  
      $row_nstockd = $reqtd->fetch();

			$this->SetFont('Arial','B',12); 
			$this->Cell(171,8,utf8_decode('Total'),1,0,'L',true);
			$this->Cell(100,8,utf8_decode($row_nstockd['total_n']),1,0,'L',true);
			$this->Ln(9);
			
			


			$this->Ln(4);













		 


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Produits envoyés à la caisse des Mamans'),0,0,'C');
			$this->Ln(8);	 	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(45,8,utf8_decode('#'),0,0,'L',true);
			$this->Cell(126,8,utf8_decode('Produits'),0,0,'L',true);
			$this->Cell(100,8,utf8_decode('Quantité'),0,0,'L',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);



      $q = 1;
      
      $sqlx = "SELECT * from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND id_depar =  '2' AND recu = 'oui' order by id_st DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_trans = $reqx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_trans['cod_produit']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();
 


			$this->Cell(45,8,utf8_decode($q++),"B",0,'L',true);
			$this->Cell(126,8,utf8_decode($row_nom_prod['nom_rec']),"B",0,'L',true);
			$this->Cell(100,8,utf8_decode($row_trans['qte']),"B",0,'L',true);
			$this->Ln(9);

      		
      		}
      	
      $sqltm = "SELECT sum(qte) AS total_n from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND id_depar =  '2' AND recu = 'oui'";
      $reqtm = $pdo->query($sqltm);  
      $row_nstockm = $reqtm->fetch();

			$this->SetFont('Arial','B',12);  
			$this->Cell(171,8,utf8_decode('Total'),1,0,'L',true);
			$this->Cell(100,8,utf8_decode($row_nstockm['total_n']),1,0,'L',true);
			$this->Ln(9);
			


			$this->Ln(4);


















		 


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Total de tous les produits envoyés'),0,0,'C');
			$this->Ln(8);	 	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(45,8,utf8_decode('#'),0,0,'L',true);
			$this->Cell(126,8,utf8_decode('Produits'),0,0,'L',true);
			$this->Cell(100,8,utf8_decode('Quantité'),0,0,'L',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);



      $q = 1;
      
      $sqlx = "SELECT * from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND recu = 'oui' order by id_st DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_trans = $reqx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_trans['cod_produit']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();
 

			$this->Cell(45,8,utf8_decode($q++),"B",0,'L',true);
			$this->Cell(126,8,utf8_decode($row_nom_prod['nom_rec']),"B",0,'L',true);
			$this->Cell(100,8,utf8_decode($row_trans['qte']),"B",0,'L',true);
			$this->Ln(9);

      		
      		}
      	
			
      $sqltt = "SELECT sum(qte) AS total_n from stock_transfere where YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND DAY(date_enreg) = '$datd' AND recu = 'oui'";
      $reqtt = $pdo->query($sqltt);  
      $row_nstocktt = $reqtt->fetch();
			
			$this->SetFont('Arial','B',12);  
			$this->Cell(171,8,utf8_decode('Total'),1,0,'L',true);
			$this->Cell(100,8,utf8_decode($row_nstocktt['total_n']),1,0,'L',true);
			$this->Ln(9);

			$this->Ln(4);



















		}
	






		function footer(){
			$this->SetY(-10);
			
			
			$this->SetFont('Arial','',11);
			$this->Cell(0,-3,'Kalipain, Page '.$this->PageNo().'/{nb}',0,0,'L');
			$this->Ln();
			//$this->Image('frame.png',40,3,11);
		}

	}
	















	$pdf=new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('L','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>