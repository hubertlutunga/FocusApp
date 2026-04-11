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



                              $cod_rec = $_GET['cod'];

                              $req="SELECT * FROM recettes WHERE cod_rec = '$cod_rec'";
                              $rec=$pdo->query($req);
                              $row_rec=$rec->fetch();

                              	if ($row_rec['classe'] == "BOUL") {
                              		$classerec = 'Boulangérie';
                              	}elseif ($row_rec['classe'] == "PAT") {
                              		$classerec = 'Pâtisserie';
                              	}elseif ($row_rec['classe'] == "RES"){
                              		$classerec = 'Restaurant';
                              	}


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
			$this->Cell(195,8,utf8_decode($row_rec['nom_rec'].' / '.$classerec),0,0,'C');
			$this->Ln(7);	
			$this->Cell(195,8,utf8_decode('Prix: '.$row_rec['pv'].' USD'),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			
		 
                              $reql="SELECT * FROM recettes WHERE cod_rec = '{$_GET['cod']}'";
                              $recl=$pdo->query($reql);
                              $row_recl=$recl->fetch();

      		$sqllogo = "SELECT * from logo where depar = '{$row_recl['classe']}'";
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
			$this->Ln(80);	
			$this->Cell(100,8,utf8_decode('Ingrédiens'),0,0,'L',true);
			$this->Cell(75,8,utf8_decode('Valeurs'),0,0,'L',true);
			$this->Ln(10);






                              $cod_recx = $_GET['cod'];

                              $reqx="SELECT * FROM recettes WHERE cod_rec = '$cod_recx'";
                              $recx=$pdo->query($reqx);
                              $row_recx=$recx->fetch();



                              $reqci="SELECT count(*) as total_img FROM menu where cod_rec = '$cod_recx'";
                              $menuci=$pdo->query($reqci);
                              $row_menuci=$menuci->fetch();
 
                              $reqreci="SELECT * FROM menu where cod_rec = '$cod_recx'";
                              $menureci=$pdo->query($reqreci);
                              $row_menureci=$menureci->fetch();

                              if ($row_menuci['total_img'] >= 1) {
                                $img = $row_menureci['image'];
                              }else{
                                $img = 'default_imgabc.jpg';
                              }

			$this->Image('../../images/restaurant/'.$img,15,65,80);
			$this->Ln(1);	



			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	


$q = 1;

while ($q <= 15) {

$col = "ing".$q++;
$coling = $row_recx[$col];

 $req="SELECT * FROM ingredien where cod_ing = '$coling'";
 $ing=$pdo->query($req);
 $row_ing=$ing->fetch();

	if($row_ing){
		$rowing = $row_ing['nom_ing'];
	}else{
		$rowing = 'Null';
	}	

 $ingredient = $rowing;

 $reqvr="SELECT * FROM valeur_recettes where cod_rec = '$cod_recx'";
 $vr=$pdo->query($reqvr);
 $row_vr=$vr->fetch();

  if ($row_vr[$col] !== "NULL") {
 $val_ing = $row_vr[$col]. " ".$row_ing['unite'];
 $style = "";
 //$style = "B";
 $line = "9";
  }else{
 $val_ing = '';
 $style = "";
 $line = "0";
  }

 



			$this->SetFont('Arial','',12);	
			$this->Cell(100,8,utf8_decode($ingredient),$style,0,'L',true);
			$this->Cell(75,8,utf8_decode($val_ing),$style,0,'L',true);
			$this->Ln($line);

			// $this->Line(50, 45, 210-50, 45);
      			


      	}

                              $reqi="SELECT * FROM recettes WHERE cod_rec = '{$_GET['cod']}'";
                              $reci=$pdo->query($reqi);
                              $row_reci=$reci->fetch();

              if (!$row_reci['desc_rec']) {
                 $desc_rec = "Aucune description";
               }elseif ($row_reci['desc_rec'] == 'NULL') {
                 $desc_rec = "Aucune description";
               }else{
                 $desc_rec = $row_reci['desc_rec'];
               } 

			$this->Ln(10);
			$this->SetFont('Arial','B',12);	
			$this->Cell(100,8,utf8_decode("Description"),0,0,'L',true);
			$this->Ln(8);
			$this->SetFont('Arial','',12);	 
			$this->MultiCell(0,5,utf8_decode($desc_rec),0,'L',2,true);
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