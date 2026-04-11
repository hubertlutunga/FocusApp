	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
  //include('../phpqrcode/qrlib.php'); 
	

	
	class myPDF extends FPDF{

		function header(){

			$this->Image('abc_filigrane.jpg',0,-5,210);
		
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
			$this->Cell(0,20,utf8_decode('BON DE SORTIE N°:'.$_GET['cod']),0,0,'C');
			$this->Ln(20);
		}
		



		function InfoInscrit($pdo){
			
						$cod = $_GET['cod'];

      $sqlsl = "SELECT * from sorti_fina where ref = '$cod'";
      $reqsl = $pdo->query($sqlsl); 
      $row_bon=$reqsl->fetch();

          $reqbenef="SELECT * FROM benef_sorti WHERE cod_bs = '{$row_bon['benefi']}'";
          $benef=$pdo->query($reqbenef);
          $row_benef=$benef->fetch();
							
						
						$date_enreg =  date('d-m-Y',strtotime($row_bon['date_enreg']));
			
						$date_sorti =  date('d-m-Y',strtotime($row_bon['date_op']));
			
								
			
			


			$this->SetFillColor(221, 221, 221);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',14);

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',11);


			$this->SetFillColor(221, 221, 221);
			$this->Cell(30,9,utf8_decode('Bénéficiaire'),0,0,'L');
			$this->Cell(160,9,utf8_decode(' '.$row_benef['noms']),0,0,'L',true);
			$this->Ln(15);


			$this->SetFillColor(255, 255, 0);
			$this->Cell(140,9,utf8_decode('Libelle'),1,0,'L');
			$this->Cell(50,9,utf8_decode('Montant'),1,0,'L');
			
			$this->Ln(10);

      $sqlslxx = "SELECT * from sorti_fina where ref = '{$_GET['cod']}' order by cod_sorti DESC";
      $reqslxx = $pdo->query($sqlslxx);  

      while ($row_sl = $reqslxx->fetch()) {

         
          $reqcatfin="SELECT * FROM categorie_fina WHERE cod_cat_menu = '{$row_sl['motif']}'";
          $catfin=$pdo->query($reqcatfin);
          $row_catfin=$catfin->fetch();


          $reqsm_list="SELECT * FROM sous_motif_sorti WHERE cod_motif = '{$row_catfin['cod_cat_menu']}'";
          $sm_list=$pdo->query($reqsm_list);
          $row_sm_list=$sm_list->fetch();


          if (!$row_sl['montant_total']) {
          		$montant = $row_sl['montant'];
          }else{

          		$montant = $row_sl['montant_total'];
          }




			$this->SetFillColor(255, 255, 0);
			$this->Cell(140,9,utf8_decode($row_sl['libelle']),'B',0,'L');
			$this->Cell(50,9,$montant.' '.$row_sl['devise'],'B',0,'L');
			$this->Ln(15);






		}




      $sqlslxxcomp = "SELECT count(*) as total_n from sorti_fina where ref = '{$_GET['cod']}' AND statut = 'ok'";
      $comp = $pdo->query($sqlslxxcomp);  
			$row_comp = $comp->fetch();



          if ($row_comp['total_n'] < 1) {

      $sqlmtav = "SELECT sum(montant) AS total_n from sorti_fina where ref = '{$_GET['cod']}'";
      $reqmtav = $pdo->query($sqlmtav);  
      $row_mtav = $reqmtav->fetch();


          }else{
          	
      $sqlmtav = "SELECT sum(montant_total) AS total_n from sorti_fina where ref = '{$_GET['cod']}' AND statut = 'ok'";
      $reqmtav = $pdo->query($sqlmtav);  
      $row_mtav = $reqmtav->fetch();

          }

          		$montant_tt = $row_mtav['total_n'];

			$this->Cell(140,9,'Total ',0,0,'R');
			$this->SetFillColor(221, 221, 221);
			$this->SetFont('Arial','B',15);
			$this->Cell(50,9,' '.$montant_tt.' '.$row_bon['devise'],0,0,'L',true);
			$this->Ln(0);






			$this->SetFillColor(255, 255, 0);
			$this->SetFont('Arial','',11);
			$this->Cell(190,9,utf8_decode('Sortie le, '.$date_sorti.', Enregistré le, '.$date_enreg),0,0,'L');
			$this->Ln(15);
			







			$this->Cell(20,6,utf8_decode('Direction'),0,0,'L');
			$this->Cell(75,6,utf8_decode('Caisse'),0,0,'R');
			$this->Cell(80,6,utf8_decode('Beneficiaire'),0,0,'R');
			$this->Ln(25);


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