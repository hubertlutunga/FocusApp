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
			$this->Cell(270,8,utf8_decode('Clients'),0,0,'C');
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
			$this->Cell(60,8,utf8_decode('Noms'),0,0,'L',true);
			$this->Cell(56,8,utf8_decode('Matricule'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Remise'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Ville'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Téléphone'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	



      $supprimer = "non";
      $sql = "SELECT * from client_boul where supprimer = '$supprimer' order by matr_cli ASC";
      $req = $pdo->query($sql);  
      while ($row_list_client = $req->fetch()) {


if ($row_list_client['remise'] <= 0) {
   $remise = '0';
 }else{
  $remise = $row_list_client['remise'];
 } 

			$this->SetFont('Arial','',12);	
			$this->Cell(60,8,utf8_decode($row_list_client['prenom'].' '.$row_list_client['nom']),"B",0,'L',true);
			$this->Cell(56,8,utf8_decode($row_list_client['matr_cli']),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode($remise),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode($row_list_client['ville']),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode($row_list_client['phone']),"B",0,'R',true);
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