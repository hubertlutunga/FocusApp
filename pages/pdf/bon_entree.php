	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
  //include('../phpqrcode/qrlib.php'); 
	

	
	class myPDF extends FPDF{

		function header(){

			$this->Image('abc_filigrane.jpg',0,0,210);
		
		}

		function head_t($pdo){
			
      $sqllogo = "SELECT * from logo ";
      $reqlogo = $pdo->query($sqllogo);  
      $row_logo = $reqlogo->fetch();

			
		}

		function infodf($pdo){



                              $req="SELECT * FROM adresse";
                              $ad=$pdo->query($req);
                              $row_ad=$ad->fetch();

                              $req="SELECT * FROM legal";
                              $leg=$pdo->query($req);
                              $row_leg=$leg->fetch();




      $sqlxc = "SELECT * from abc_user WHERE identifiant = '{$_SESSION['id']}'";
      $reqxc = $pdo->query($sqlxc);  
      $row_agentxc = $reqxc->fetch();



		

			$this->SetFont('Arial','',14);
			$this->Cell(0,6,utf8_decode('ABC RDC, NUMIMPOT: '.$row_leg['num_impot'].', RCCM: '.$row_leg['rccm']),0,0,'C');
			$this->Ln(3);
			$this->SetFont('Arial','B',14);
			$this->Cell(0,20,utf8_decode('BON D\'ENTREE N°:'.$_GET['cod']),0,0,'C');
			$this->Ln(20);
		}
		



		function InfoInscrit($pdo){
			
						$cod = $_GET['cod'];


      $sqlx = "SELECT * from entree_fina where cod_entree = '$cod'";

          $reqx=$pdo->query($sqlx);
          $row_list_entre=$reqx->fetch();


         
          $reqcatfin="SELECT * FROM categorie_fina WHERE cod_cat_menu = '{$row_list_entre['provenance']}'";
          $catfin=$pdo->query($reqcatfin);
          $row_catfin=$catfin->fetch();
							
						
						$date_enreg =  date('d-m-Y',strtotime($row_list_entre['date_enreg']));
			

                              $reqsm="SELECT * FROM sous_motif_sorti WHERE cod_sm = '{$row_list_entre['sous_prov']}'";
                              $sm=$pdo->query($reqsm);
                               $row_sm=$sm->fetch();
								
			
			


			$this->SetFillColor(221, 221, 221);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',11);


			$this->SetFillColor(221, 221, 221);
			$this->Cell(30,9,utf8_decode('Provenance'),0,0,'L');
			$this->Cell(160,9,utf8_decode(' '.$row_catfin['nom'].' / '.$row_sm['nom_sm']),0,0,'L',true);
			$this->Ln(11);

			$this->Cell(30,9,utf8_decode('Montant'),0,0,'L');
			$this->Cell(160,9,$row_list_entre['montant'].' '.$row_list_entre['monnais'],0,0,'L',true);
			$this->Ln(11);

			$this->Cell(30,9,utf8_decode('Libelle'),0,0,'L');
			$this->Cell(160,9,utf8_decode($row_list_entre['libelle']),0,0,'L',true);
			$this->Ln(11);







			$this->SetFillColor(255, 255, 0);
			$this->SetFont('Arial','',11);
			$this->Cell(190,9,utf8_decode('Date d\'enregistrement: '.$date_enreg),0,0,'L');
			$this->Ln(15);
			







			$this->Cell(110,6,utf8_decode('Caisse'),0,0,'L');
			$this->Cell(65,6,utf8_decode('Porteur'),0,0,'L');
			$this->Ln(30);

			$this->Cell(275,-6,utf8_decode('_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_'),0,0,'L');
			$this->Ln();
						
			



		}





		
		
	
	}
	
	
	$pdf=new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('P','A4','0');
	$pdf->header();
	$pdf->infodf($pdo);
	$pdf->InfoInscrit($pdo);
	$pdf->head_t($pdo);
	$pdf->Output();
	

?>