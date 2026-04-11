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
			$this->Cell(270,8,utf8_decode('Rapport de production'),0,0,'C');
			$this->Ln(8);	
			$this->Cell(270,8,utf8_decode('('.date('M').' - '.date('Y').')'),0,0,'C');
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
			$this->Cell(25,8,utf8_decode('Réf'),0,0,'L',true);
			$this->Cell(35,8,utf8_decode('Post / Pré'),0,0,'L',true);
			$this->Cell(71,8,utf8_decode('Recette'),0,0,'L',true);
			$this->Cell(40,8,utf8_decode('Par'),0,0,'L',true);
			$this->Cell(55,8,utf8_decode('Obs'),0,0,'L',true);
			$this->Cell(20,8,utf8_decode('Prod'),0,0,'L',true);
			$this->Cell(20,8,utf8_decode('Date'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	


      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');

      $statut = "oui";


      $sql_p = "SELECT * from production where statut = '$statut' AND YEAR(date_production) = '$daty' AND Month(date_production) = '$datm'  order by code_production DESC";
      $req_p = $pdo->query($sql_p);  
      while ($row_list_product = $req_p->fetch()) {

      $sqsl = "SELECT * from abc_user WHERE matr_user = '{$row_list_product['code_agent']}'";
      $resq = $pdo->query($sqsl);  
      $ussession_prod = $resq->fetch();


      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_list_product['cod_rec']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();

      $sqlrp = "SELECT count(*) As total_rp from rapport_production WHERE cod_production = '{$row_list_product['code_production']}'";
      $reqrp = $pdo->query($sqlrp);  
      $row_exist_rapport = $reqrp->fetch();

      $sqlrpi = "SELECT * from rapport_production WHERE cod_production = '{$row_list_product['code_production']}'";
      $reqrpi = $pdo->query($sqlrpi);  
      $row_exist_rapport_i = $reqrpi->fetch();


										if ($row_exist_rapport['total_rp'] < 1) {

                                           $rapp = " - ";

                                          }else{

                                           $rapp = "Ok";

                                           if ($row_exist_rapport_i['obs'] == 'NULL') {
                                           	
                                           $obs = "Pas d'observation";
                                           }else{
                                           $obs = $row_exist_rapport_i['obs'];
                                           }
                                        

                                           }

										   if(is_numeric($row_list_product['qteproduct'])){
											$qteproduct = $row_list_product['qteproduct']; 
										   }else{
											$qteproduct = '0';  
										   }

										   if($row_exist_rapport_i){ 
											$nbag = $row_exist_rapport_i['nomb_bag'];
										   }else{ 
											$nbag = '-';
										   }



			$this->SetFont('Arial','',12);	
			$this->Cell(25,8,utf8_decode($row_list_product['code_production']),"B",0,'L',true);
			$this->Cell(35,8,utf8_decode($nbag.' / '.$qteproduct),"B",0,'L',true);
			$this->Cell(71,8,utf8_decode($row_rec['nom_rec']),"B",0,'L',true);
			$this->Cell(40,8,utf8_decode($ussession_prod['prenom'].' '.$ussession_prod['nom']),"B",0,'L',true);
			$this->Cell(55,8,utf8_decode($obs),"B",0,'L',true);
			$this->Cell(20,8,utf8_decode($rapp),"B",0,'L',true);
			$this->Cell(20,8,utf8_decode(date('d M Y',strtotime($row_list_product['date_production']))),"B",0,'R',true);
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