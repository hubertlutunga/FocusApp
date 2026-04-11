	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
    include('../qrscan/phpqrcode/qrlib.php'); 
	



	class myPDF extends FPDF{


		
		function infodf($pdo){



			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);	
			$this->Ln(55);	
                       

		}


		function head_t($pdo){
			
		
      		$sqllogo = "SELECT * from logo ";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

		
			$this->Image('../../images/'.$row_logo['logo'],22,5,35);
			$this->Ln(1);
										
			
		}


		function Infofact($pdo){
			
				
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


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);
			$this->SetMargins(0,0,0,0);
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(0.1);				
			$this->Cell(0,5,utf8_decode('BON DE LIVRAISON No.: '.strtoupper($row_list_comget['num_fact'])),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,5,utf8_decode('Date: '.date('d/m/Y à H:i',strtotime($row_list_comget['date_enreg']))),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,5,utf8_decode('Client(e): '.$row_info_client['prenom'].' '.$row_info_client['nom']),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,5,utf8_decode('Caisse: '.$row_agent['prenom'].' '.$row_agent['nom']),0,0,'C');
			$this->Ln(8);


			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);	
			$this->Cell(83,3,'Qte / Articles',0,0,'C',true);
			$this->Ln(6);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	




      $sqldpx = "SELECT * from commandes where num_fact = '{$_GET['ref']}' order by code_com ASC";
      $reqdpx = $pdo->query($sqldpx);  
      while ($row_dp = $reqdpx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_dp['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_prod = $reqs->fetch();
      		

      			
			$this->SetAutoPageBreak(true,0);
			$this->Cell(83,3,utf8_decode('('.$row_dp['qte'].') '.$row_prod['nom_rec']),0,0,'C',true);
			$this->Ln(5);
			$this->Cell(83,3,utf8_decode('Qte livrée:..........................'),0,0,'C',true);
			$this->Ln(5);

      		}


			$this->Ln(3);






 

			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->Cell(83,3,'Signature Caisse',0,0,'C',true);
			$this->Ln(16); 
 

			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->Cell(83,3,'Nom et Signature Livreur',0,0,'C',true);
			$this->Ln(16); 

			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->Cell(83,3,'Nom et Signature Client',0,0,'C',true);
			$this->Ln(16); 



			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->Cell(83,3,'_________________________________________________________',0,0,'C',true);
			$this->Ln(6); 










		}
	



		function fqr($pdo){

			

                  $PNG_TEMP_DIR = 'temp/';
                  if (!file_exists($PNG_TEMP_DIR))
                      mkdir($PNG_TEMP_DIR);


                  $codeString = 'https://kalipain.com/app/adminabc/index.php?page=analyseqrdirect&search='.$_GET['ref'].'';

                  $filename = $PNG_TEMP_DIR . 'fp_qr.png';

                  $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';

                  QRcode::png($codeString, $filename);


			$this->Image($PNG_TEMP_DIR . basename($filename),18,20,45);

			
		}

 





	}
	










	$pdf=new myPDF();
	$pdf->AddPage('P', [200,80]);
	//$pdf->AddPage('L','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo); 
	$pdf->fqr($pdo);
	$pdf->Output();
	

?>