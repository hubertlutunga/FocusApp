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
			$this->SetFont('Arial','B',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Les entrées dans le Stock'),0,0,'C');
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




      $sql = "SELECT * from abc_user WHERE identifiant = '{$_SESSION['id']}'";
      $req = $pdo->query($sql);  
      $row_agent = $req->fetch();



			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(130,8,utf8_decode('Qte/Pds/Produit/Marque'),0,0,'L',true);
			$this->Cell(56,8,utf8_decode('P.U / P.T'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Four.'),0,0,'L',true);
			$this->Cell(30,8,utf8_decode('Date.'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	



 $prix_achat_total = "NULL";
      
      $sqlx = "SELECT * from stock where prix_achat_total != '$prix_achat_total' order by code_stock DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_det_stock = $reqx->fetch()) {

                               $reqw="SELECT * FROM ingredien where cod_ing = '{$row_det_stock['code_produit']}'";
                               $ingw=$pdo->query($reqw);
                               $row_ing=$ingw->fetch();

     $reqfourn="SELECT * FROM fournisseur where code_fourn = '{$row_det_stock['code_fourn']}'";
     $sqlfourn=$pdo->query($reqfourn);
     $row_fourn=$sqlfourn->fetch();

 			$reqmarq="SELECT * FROM marque_produit WHERE cod_mp = '{$row_det_stock['nom_produit']}'";
                              $marq=$pdo->query($reqmarq);
                              $row_marq=$marq->fetch();


			$this->SetFont('Arial','',12);	
			$this->Cell(130,8,utf8_decode('('.$row_det_stock['poids_total'].' '.$row_ing['unite'].') '.$row_ing['nom_ing'].' '.$row_marq['nom_mp']),"B",0,'L',true);
			$this->Cell(56,8,utf8_decode($row_det_stock['prix_achat'].' / '.$row_det_stock['prix_achat_total']),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode($row_fourn['denom_sociale']),"B",0,'L',true);
			$this->Cell(30,8,utf8_decode(date('d M Y',strtotime($row_det_stock['date_enreg']))),"B",0,'R',true);
			$this->Ln(9);

			// $this->Line(50, 45, 210-50, 45);
      			


      	}


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