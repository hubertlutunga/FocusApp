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
			
				
      		$ref = $_GET['ref'];


      $sql = "SELECT * from livraison_boul  where cod_livb = '$ref';";
      $req = $pdo->query($sql);  
      $row_list_com = $req->fetch();

      $sql = "SELECT * from abc_user WHERE matr_user = '{$row_list_com['agent']}'";
      $req = $pdo->query($sql);  
      $row_agent = $req->fetch();


      $sqlgets = "SELECT * from commandes  where code_com = '{$row_list_com['code_com']}'";
      $reqgets = $pdo->query($sqlgets);  
      $row_li_comgs = $reqgets->fetch();

      $sqlx = "SELECT * from client_boul WHERE matr_cli = '{$row_li_comgs['code_cli']}'";
      $reqx = $pdo->query($sqlx);  
      $row_info_client = $reqx->fetch();


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->SetMargins(0,0,0,0);
			$this->Cell(0,0,utf8_decode(''),0,0,'C');
			$this->Ln(0.1);
			$this->Cell(60,0,utf8_decode('LIVRAISON N°: '.strtoupper($row_list_com['num_fact'])),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Date: '.date('d/m/Y à H:i',strtotime($row_list_com['date_livb']))),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Dépôt: '.$row_agent['prenom'].' '.$row_agent['nom']),0,0,'C');
			$this->Ln(4);		
			$this->Cell(60,0,utf8_decode('Client(e): '.$row_info_client['prenom'].' '.$row_info_client['nom']),0,0,'C');
			$this->Ln(8);


			$this->SetMargins(0,0,0,0);
			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->Ln(0.1);
			$this->Cell(60,3,utf8_decode('Qte / Articles'),0,0,'C',true);
			$this->Ln(6);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);	




      $sqlbc = "SELECT * from livraison_boul  where num_fact = '{$row_list_com['num_fact']}' order by cod_livb DESC";
      $reqbc = $pdo->query($sqlbc);  
      while ($row_list_combc = $reqbc->fetch()) {
      		
      $sqls="SELECT * FROM recettes where cod_rec = '{$row_list_combc['cod_prod']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();


      $sqlget = "SELECT * from commandes  where code_com = '{$row_list_combc['code_com']}'";
      $reqget = $pdo->query($sqlget);  
      $row_li_comg = $reqget->fetch();


			$this->Cell(60,3,utf8_decode($row_li_comg['qte'].' / '.$row_nom_prod['nom_rec']),0,0,'C',true);
			$this->Ln(5);

      		}


			$this->Ln(6);











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