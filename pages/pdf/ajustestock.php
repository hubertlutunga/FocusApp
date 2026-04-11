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
			$this->Cell(270,8,utf8_decode('Les ajustements dans le Stock'),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			
		
			$this->Image('../../images/logo_toppain_cl.png',8,7,40);
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
			$this->Cell(120,8,utf8_decode('Qte/Pds/Produit/Marque'),0,0,'L',true);
			$this->Cell(96,8,utf8_decode('Raison'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Date / Agent'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	



      $raison = 'Production';


      $sqlx = "SELECT * from stock_utiliser where raison != '$raison' order by cod_su DESC";
      $reqx = $pdo->query($sqlx);  
      while ($row_s_ajuster = $reqx->fetch()) {


     $req="SELECT * FROM ingredien where cod_ing = '{$row_s_ajuster['cod_ing']}'";
     $ing=$pdo->query($req);
     $row_ing=$ing->fetch();


      $sqlag = "SELECT * from abc_user WHERE matr_user = '{$row_s_ajuster['agent']}'";
      $reqag = $pdo->query($sqlag);  
      $row_agent = $reqag->fetch();


                              $mp = $row_s_ajuster['cod_mp'];
                              $reqmp="SELECT * FROM marque_produit WHERE cod_mp = '$mp'";
                              $mp=$pdo->query($reqmp);
                              $row_mp=$mp->fetch();


			$this->SetFont('Arial','',12);	
			$this->Cell(120,8,utf8_decode('('.$row_s_ajuster['su'].' '.$row_ing['unite'].') '.$row_ing['nom_ing'].' / '.$row_mp['nom_mp']),"B",0,'L',true);
			$this->Cell(96,8,utf8_decode($row_s_ajuster['raison']),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode(date('d M Y',strtotime($row_s_ajuster['date_su'])).' / '.$row_agent['prenom'].' '.$row_agent['nom']),"B",0,'R',true);
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