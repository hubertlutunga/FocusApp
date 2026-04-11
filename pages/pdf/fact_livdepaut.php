	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
       include('../qrscan/phpqrcode/qrlib.php'); 
	



	class myPDF extends FPDF{


		
		function infodf($pdo){



			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);	
			$this->Ln(15);	

		}


		function head_t($pdo){
			
				
      		$sqllogo = "SELECT * from logo ";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

		
			$this->Image('../../images/'.$row_logo['logo'],15,5,30);
			$this->Ln(1);

					
			
			
		}


		function Infofact($pdo){
			
				

      $codcom = $_GET['codcom'];
      $codliv = $_GET['ref'];


//------------livraison---------------

      $sqldl = "SELECT * from com_livraison WHERE cod_cl = '$codliv'";
      $dl = $pdo->query($sqldl);  
      $row_dl = $dl->fetch();


      $sqlv = "SELECT * from abc_user WHERE matr_user = '{$row_dl['agent']}'";
      $reqv = $pdo->query($sqlv);  
      $row_agent = $reqv->fetch();

//-----------------------------------

      $sqlx = "SELECT * from commandes where code_com = '$codcom'";

      $reqx=$pdo->query($sqlx);
      $row_list_com = $reqx->fetch();


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();


      $sqlc = "SELECT * from client_boul WHERE matr_cli = '{$row_list_com['code_cli']}'";
      $reqc = $pdo->query($sqlc);  
      $row_info_client = $reqc->fetch();


      $req="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}' ORDER by cod_rec ASC";
      $pv=$pdo->query($req);
      $row_pv=$pv->fetch();


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->SetMargins(0,0,0,0);
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(0.1);
			$this->Cell(60,0,utf8_decode('LIVRAISON N°: '.$codliv),0,0,'C');
			$this->Ln(7);
			$this->Cell(60,0,utf8_decode('Client(e): '.$row_info_client['prenom'].' '.$row_info_client['nom']),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Date: '.date('d M Y à H:i',strtotime($row_dl['date_liv']))),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Quanité commandé: '.$row_dl['qte_com']),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Quanité réçu: '.$row_dl['qte_liv']),0,0,'C');	
			$this->Cell(60,0,utf8_decode('Livreur: '.$row_agent['prenom'].' '.$row_agent['nom']),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Receptioniste: '.$row_dl['recep']),0,0,'C');
			$this->Ln(7);		
			$this->Cell(60,0,utf8_decode('Signature'),0,0,'C');
			$this->Ln(8);











	}
	
}
	
















	$pdf=new myPDF();
	$pdf->AddPage('P', [100,60]);
	//$pdf->AddPage('L','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>