	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
       include('../qrscan/phpqrcode/qrlib.php');
	





	class myPDF extends FPDF{
		function header(){
		}
		


		function Infomemb($pdo){
			
			$this->Image('carte_memb.jpg',0,0,85);
			
				

	}
	



		function Infomembcc($pdo){
			
				
		//	$this->Image('frame.png',48.4,26.2,17.7);

							
                  $PNG_TEMP_DIR = 'temp/';
                  if (!file_exists($PNG_TEMP_DIR))
                      mkdir($PNG_TEMP_DIR);


                  $codeString = 'https://kalipain.com/';

                  $filename = $PNG_TEMP_DIR . 'fp_qr.png';

                  $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';

                  QRcode::png($codeString, $filename);


									$this->Image($PNG_TEMP_DIR . basename($filename),48.4,26.2,17.7);

									
	

			$matr_cli = $_GET['cod'];
      $sql = "SELECT * from client_restau WHERE code_cli = '$matr_cli'";
      $req = $pdo->query($sql);  
      $row_info_client = $req->fetch();






			$this->SetTextColor(251, 186, 56);
			$this->SetFont('Arial','',9);
			$this->SetMargins(0,0,0,0);		
			$this->SetAutoPageBreak(true,0);		
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(1);					
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(5);			
			$this->SetTextColor(251, 186, 56);
			$this->SetFont('Arial','',13);
			$this->SetFontSpacing(3);
			$this->Cell(62,0,$row_info_client['code_promo'],0,0,'R');
			$this->Ln(10);		
			$this->SetFontSpacing(0);	
			$this->Ln(23);			
			$this->SetFont('Arial','',6);
			$this->SetFontSpacing(3);
			$this->Cell(58,0,'exp: '.date('d/m/Y',strtotime($row_info_client['dateexp'])),0,0,'R');
			$this->Ln(4);			
			$this->SetFontSpacing(0);
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
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
			
			$this->Image('carte_dos_fid.jpg',0,0,85);




				






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