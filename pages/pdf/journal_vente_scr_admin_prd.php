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
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(270,8,utf8_decode('Journal des ventes'),0,0,'C');
			$this->Ln(8);	
			$this->Cell(270,8,utf8_decode('('.date('d M Y',strtotime($_GET['da'])).' au '.date('d M Y',strtotime($_GET['db'])).')'),0,0,'C');
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




			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(66,8,utf8_decode('Client'),0,0,'L',true);
			$this->Cell(85,8,utf8_decode('Recette'),0,0,'L',true);
			$this->Cell(20,8,utf8_decode('Qte'),0,0,'L',true);
			$this->Cell(30,8,utf8_decode('PU'),0,0,'L',true);
			$this->Cell(30,8,utf8_decode('PT'),0,0,'L',true);
			$this->Cell(20,8,utf8_decode('Liv'),0,0,'L',true);
			$this->Cell(20,8,utf8_decode('Date'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

          $da = $_GET['da'];
          $db = $_GET['db'];


      $sqlx = "SELECT * from commandes where date_enreg between '$da' and '$db' order by code_com DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_list_com = $reqx->fetch()) {


      $sqls="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();


      $sqlc = "SELECT * from client_boul WHERE matr_cli = '{$row_list_com['code_cli']}'";
      $reqc = $pdo->query($sqlc);  
      $row_info_client = $reqc->fetch();


      $req="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}' ORDER by cod_rec ASC";
      $pv=$pdo->query($req);
      $row_pv=$pv->fetch();


      $total_somme = $row_list_com['qte'] * $row_pv['pv'];


      $sqlw = "SELECT * from livraison_boul  where code_com = '{$row_list_com['code_com']}'";
      $reqw = $pdo->query($sqlw);  
      $row_com = $reqw->fetch();

      $sqlv = "SELECT * from abc_user WHERE matr_user = '{$row_com['agent']}'";
      $reqv = $pdo->query($sqlv);  
      $row_agent = $reqv->fetch();

      											if ($row_list_com['statut'] == "liv") {
                               $liv = 'Ok';
                             }else{
                               $liv = '-';
                             } 

			$this->Cell(66,8,utf8_decode($row_info_client['prenom'].' '.$row_info_client['nom']),"B",0,'L',true);
			$this->Cell(85,8,utf8_decode($row_nom_prod['nom_rec']),"B",0,'L',true);
			$this->Cell(20,8,utf8_decode($row_list_com['qte']),"B",0,'L',true);
			$this->Cell(30,8,utf8_decode($row_list_com['pv']),"B",0,'L',true);
			$this->Cell(30,8,utf8_decode($row_list_com['pvt']),"B",0,'L',true);
			$this->Cell(20,8,utf8_decode($liv),"B",0,'L',true);
			$this->Cell(20,8,utf8_decode(date('d M Y',strtotime($row_list_com['date_enreg']))),"B",0,'R',true);
			$this->Ln(9);

      		
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