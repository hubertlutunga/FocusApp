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
			$this->Cell(195,8,utf8_decode('Informtations du fournisseur'),0,0,'C');
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
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	



      $code_fourn = $_GET['cod'];
      $supprimer = 'non';
      $sql = "SELECT * from fournisseur where code_fourn = '$code_fourn' AND supprimer = '$supprimer'";
      $req = $pdo->query($sql);  
      $row_list_fournisseur = $req->fetch();


      $sqlp = "SELECT * from pays where alpha3 = '{$row_list_fournisseur['pays']}'";
      $reqp = $pdo->query($sqlp);  
      $row_pays = $reqp->fetch();



			$this->SetFont('Arial','',12);	
			$this->Cell(175,8,utf8_decode('Dénom sociale: '.$row_list_fournisseur['denom_sociale']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Nom: '.$row_list_fournisseur['noms']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Pays: '.$row_pays['nom_fr_fr']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Ville: '.$row_list_fournisseur['ville']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Adresse: '.$row_list_fournisseur['adresse']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Adresse E-mail: '.$row_list_fournisseur['email']),0,0,'L',true);
			$this->Ln(9);
			$this->Cell(175,8,utf8_decode('Téléphone: '.$row_list_fournisseur['phone']),0,0,'L',true);
			$this->Ln(9);

			// $this->Line(50, 45, 210-50, 45);
      			




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
	$pdf->AddPage('P','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>