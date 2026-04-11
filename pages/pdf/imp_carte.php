	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
       include('../qrscan/phpqrcode/qrlib.php');
	



	class myPDF extends FPDF{
		function header(){
		}
		


		function Infomemb($pdo){
			
			$this->Image('carte_dos.jpg',0,0,85);
			
				

	}
	



		function Infomembcc($pdo){
			
				
			$matr_cli = $_GET['cod'];
      $sql = "SELECT * from client_boul WHERE matr_cli = '$matr_cli'";
      $req = $pdo->query($sql);  
      $row_info_client = $req->fetch();

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);
			$this->SetMargins(0,0,0,0);		
			$this->SetAutoPageBreak(true,0);		
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(1);					
			$this->Cell(0,0,utf8_decode('CARTE D\'ABONNEMENT'),0,0,'C');
			$this->Ln(7);			
			$this->SetTextColor(16, 47, 148);
			$this->SetFont('Arial','B',23);
			$this->Cell(0,0,utf8_decode(strtoupper($row_info_client['matr_cli'])),0,0,'C');
			$this->SetTextColor(0, 0, 0);
			$this->Ln(7);			
			$this->SetFont('Arial','B',9);
			$this->Cell(0,0,utf8_decode(strtoupper($row_info_client['prenom'].' '.$row_info_client['nom'])),0,0,'C');
			$this->Ln(13);			
			$this->SetFont('Arial','',7);
			$this->Cell(0,0,utf8_decode($row_info_client['adresse']),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,utf8_decode($row_info_client['phone']),0,0,'C');
			$this->Ln(16);			
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(4);	




	}






		function verso($pdo){
			

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);
			
			$this->Image('carte_dev.jpg',0,0,85);




                  $PNG_TEMP_DIR = 'temp/';
                  if (!file_exists($PNG_TEMP_DIR))
                      mkdir($PNG_TEMP_DIR);


                  $codeString = 'https://kalipain.com/app/adminabc/index.php?page=analyseqrdirect_cli&cod='.$_GET['cod'].'';

                  $filename = $PNG_TEMP_DIR . 'fp_qr.png';

                  $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';

                  QRcode::png($codeString, $filename);


									$this->Image($PNG_TEMP_DIR . basename($filename),42,7.5,40);
	

				




		//	$this->Image('frame.png',42,7.5,40);


						}


}
	





	$pdf=new myPDF();
	$pdf->AddPage('L', [85, 54.4]);
	//$pdf->AddPage('L','A5','0');
	$pdf->Infomemb($pdo);
	$pdf->Infomembcc($pdo);
	$pdf->verso($pdo);
	$pdf->Output();
	

?>