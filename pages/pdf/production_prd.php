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
			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(0);						
			$this->Cell(195,8,mb_convert_encoding('RD Congo - '.$row_ad['ville'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('NUMIMPOT: '.$row_leg['num_impot'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('RCCM: '.$row_leg['rccm'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('Tél.: '.$row_ad['phone'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(12);			

					$class = $_GET['class'];

					if ($class == 'PAT') {
						$nclass = "Productions de la Patisserie";
					}elseif($class == 'BOUL'){
							$nclass = "Productions de la Boulangerie";
						}elseif ($class == 'CUIS') {
								$nclass = "Productions de la Cuisine";
							}else{
									$nclass = "Productions";
									}

			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);		
			$this->Cell(195,8,$nclass.' du '.date('d/m/Y',strtotime($_GET['da'])).' au '.date('d/m/Y',strtotime($_GET['db'])),0,0,'C');
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
			
	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(0);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      		
      		$db = $_GET['db'];
      		$da = $_GET['da'];
			$class = $_GET['class'];

			
			if ($class == 'PAT') {
				$cond = "where classe = 'PAT' AND";
			}elseif($class == 'BOUL'){
				$cond = "where classe = 'BOUL' AND";
				}elseif ($class == 'CUIS') {
					$cond = "where classe = 'RES' AND";
					}else{
						$cond = "where";
						}

	 $statut = 'oui';
	 $raison = "production";






      $sqlistv = "SELECT * from production $cond date_production between '$da' and '$db'  order by code_production DESC";
      $reqlistv = $pdo->query($sqlistv);  
      while ($row_list_product = $reqlistv->fetch()) {

      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_list_product['cod_rec']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();


			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);




	
      $sqlrp = "SELECT count(*) As total_rp from rapport_production WHERE cod_production = '{$row_list_product['code_production']}'";
      $reqrp = $pdo->query($sqlrp);  
      $row_exist_rapport = $reqrp->fetch();



       if ($row_exist_rapport['total_rp'] < 1) {
		$deja = 'Production N° '.$row_list_product['code_production'].' / '.$row_list_product['qteproduct'].' / '.$row_rec['nom_rec'];
 	}else{
		$deja = 'Production N° '.$row_list_product['code_production'].' / '.$row_list_product['qteproduct'].' / '.$row_rec['nom_rec'].' - déjà effecutée';
 	}




			$this->Cell(190,8,mb_convert_encoding($deja, 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Ln(10);
			


 
      $sqlsu = "SELECT * from stock_utiliser where cod_production = '{$row_list_product['code_production']}' AND raison = '$raison' order by cod_su ASC";
      $reqsu = $pdo->query($sqlsu);  
      while ($row_su = $reqsu->fetch()) {


      	$colin = $row_su['cod_ing'];
 		$req="SELECT * FROM ingredien where cod_ing = '$colin'";
 		$ing=$pdo->query($req);
 		$row_ing=$ing->fetch(); 


      $sqlstock_ut = "SELECT * from stock_utiliser where cod_production = '{$row_list_product['code_production']}' AND cod_ing = '$colin'";
      $stock_ut = $pdo->query($sqlstock_ut);  
      $row_stock_ut = $stock_ut->fetch();

      $sqlmp = "SELECT * from marque_produit where cod_mp = '{$row_stock_ut['cod_mp']}'";
      $mp = $pdo->query($sqlmp);  
      $row_momarp = $mp->fetch(); 

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

			if(is_numeric($row_su['su'])){

				$rowsu = $row_su['su'];
		
			  }else{ 
		
				$rowsu = '0';
		
			  }

			  if($row_momarp){
		
				$rowmomarp = $row_momarp['nom_mp'];
		
			  }else{ 
		
				$rowmomarp = '(non specifiée)';
		
			  }
		
		
			  if ($row_ing['cat_ing'] == 'ING' || $row_ing['cat_ing'] === NULL) {
				$rowing = $row_ing['nom_ing'] . ' (' . $rowmomarp . ')';
				} else {
					$rowing = $row_ing['nom_ing'] . ' (Sous recette)';
				}
		

			$this->Cell(140,9,mb_convert_encoding($rowing, 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(35,9,number_format($rowsu, 4, '.', ' '),0,0,'R',true);
			$this->Cell(20,9,$row_ing['unite'],0,0,'L',true);
			$this->Ln(8);

      		
      		}


			$this->Ln(4);
      	}



			$this->Ln(7);

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',16);	
			$this->Cell(190,8,'TOTAL','B',1,'R',true);
			$this->Ln(4);

/*
			




*/









      		$db = $_GET['db'];
      		$da = $_GET['da'];
			
			  $class = $_GET['class'];

			
			  if ($class == 'PAT') {
				  $cond = "where classe = 'PAT' AND";
			  }elseif($class == 'BOUL'){
				  $cond = "where classe = 'BOUL' AND"; 
				  }elseif ($class == 'CUIS') {
					  $cond = "where classe = 'RES' AND";
					  }else{
						  $cond = "where";
						  }

      $sqlli = "SELECT DISTINCT  cod_ing,cod_mp from stock_utiliser $cond date_su between '$da' and '$db' AND raison = '$raison' order by cod_ing ASC";
      $reqli = $pdo->query($sqlli);  
      while ($row_list_ing = $reqli->fetch()) {


      $sqlmpst = "SELECT * from marque_produit where cod_mp = '{$row_list_ing['cod_mp']}'";
      $mpst = $pdo->query($sqlmpst);  
      $row_momarpst = $mpst->fetch();


      	$colin = $row_list_ing['cod_ing'];
 		$req_li="SELECT * FROM ingredien where cod_ing = '$colin'";
 		$ing_li=$pdo->query($req_li);
 		$row_ing_li=$ing_li->fetch();

 		$cod_ing = $row_list_ing['cod_ing'];

      $marq = $row_list_ing['cod_mp'];

      $sqlto = "SELECT sum(su) AS total_n from stock_utiliser $cond cod_ing = '$cod_ing' AND cod_mp = '$marq' AND date_su between '$da' and '$db'";
      
      $reqto = $pdo->query($sqlto);  
      $row_suto = $reqto->fetch();

	
	  if($row_suto['total_n']){

		$total_sorti = $row_suto['total_n'];

	  }else{ 

		$total_sorti = '0';

	  }

	  if($row_momarpst){

		$rowmomarpst = $row_momarpst['nom_mp'];

	  }else{ 

		$rowmomarpst = 'non préciséé';

	  }


	  if ($row_ing_li['cat_ing'] == 'ING' || $row_ing_li['cat_ing'] === NULL) {
		$rowingli = $row_ing_li['nom_ing'] . ' (' . $rowmomarpst . ')';
		} else {
			$rowingli = $row_ing_li['nom_ing'] . ' (Sous recette)';
		}

	  


			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	
			$this->Cell(160,8,mb_convert_encoding($rowingli, 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->SetFont('Arial','B',12);
			$this->Cell(25,8,number_format($total_sorti, 4, '.', ' ').' '.$row_ing_li['unite'],0,0,'L',true);
			
			$this->Ln(7);



		   }


		}
	






		function footer(){
			$this->SetY(-10);
			
			
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,7,7,7,true);
			$this->Cell(0,-3,'Kalipain, Page '.$this->PageNo().'/{nb}',0,0,'R');
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