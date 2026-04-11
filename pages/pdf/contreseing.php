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



                              $code = $_GET['cod'];

                             


      $sqlst = "SELECT * from stock_trans_boulpat where id_tpt = '$code'";
      $reqst = $pdo->query($sqlst);  
      $row_st = $reqst->fetch();


      if ($row_st['cod_marq']) {

      $reqing="SELECT * FROM ingredien where cod_ing = '{$row_st['cod_prod']}'";
      $cating=$pdo->query($reqing);
      $row_ing=$cating->fetch();

      $produit = $row_ing['nom_ing'];

      }elseif (!$row_st['cod_marq']){

      $req="SELECT * FROM menu where cod_rec = '{$row_st['cod_prod']}'";
      $menu=$pdo->query($req);
      $row_menu=$menu->fetch();
 
      $produit = $row_menu['nom'];

     	}


      $reqmp="SELECT * FROM marque_produit WHERE cod_mp = '{$row_st['cod_marq']}'";
      $mp=$pdo->query($reqmp);
      $row_mp=$mp->fetch();

      $sqlagrecu = "SELECT * from abc_user WHERE matr_user = '{$row_st['agent_recu']}'";
      $agrecu = $pdo->query($sqlagrecu);  
      $row_agrecu = $agrecu->fetch();

      $sqlagtra = "SELECT * from abc_user WHERE matr_user = '{$row_st['agent']}'";
      $agtra = $pdo->query($sqlagtra);  
      $row_agtra = $agtra->fetch();



                              	if ($row_st['destination'] == "2") {
                              		$depart = 'la Boulangérie';
                              	}elseif ($row_st['destination'] == "7") {
                              		$depart = 'la Pâtisserie';
                              	}elseif ($row_st['destination'] == "3"){
                              		$depart = 'le Restaurant';
                              	}elseif ($row_st['destination'] == "15"){
                              		$depart = 'la Cuisine';
                              	}


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,20,0,0,true);		
			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(0);						
			$this->Cell(195,8,'RD Congo - '.$row_ad['ville'],0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,'NUMIMPOT: '.$row_leg['num_impot'],0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,'RCCM: '.$row_leg['rccm'],0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,utf8_decode('Tél.: '.$row_ad['phone']),0,0,'R');
			$this->Ln(12);





			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(195,8,'TRANSFERT DE STOCK',0,0,'C');
			$this->Ln(7);	
			$this->Cell(195,8,utf8_decode($produit.' vers '.$depart),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			 
      		$sqllogo = "SELECT * from logo where depar = 'BOUL'";
      		$reqlogo = $pdo->query($sqllogo);  
      		$row_logo = $reqlogo->fetch();

		
			$this->Image('../../images/'.$row_logo['logo'],8,7,40);
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);	
		}


		function Infofact($pdo){
			
				
 
 $code = $_GET['cod'];

                             


      $sqlst = "SELECT * from stock_trans_boulpat where id_tpt = '$code'";
      $reqst = $pdo->query($sqlst);  
      $row_st = $reqst->fetch();


      if ($row_st['cod_marq']) {

      $reqing="SELECT * FROM ingredien where cod_ing = '{$row_st['cod_prod']}'";
      $cating=$pdo->query($reqing);
      $row_ing=$cating->fetch();

      $reqmp="SELECT * FROM marque_produit WHERE cod_mp = '{$row_st['cod_marq']}'";
      $mp=$pdo->query($reqmp);
      $row_mp=$mp->fetch();

      $unite = $row_ing['unite'];

      $produit = $row_ing['nom_ing'].' de marque '.$row_mp['nom_mp'];

      }elseif (!$row_st['cod_marq']){

      $req="SELECT * FROM menu where cod_rec = '{$row_st['cod_prod']}'";
      $menu=$pdo->query($req);
      $row_menu=$menu->fetch();
 
      $unite = '';
      
      $produit = $row_menu['nom'];

     	}



      $sqlagrecu = "SELECT * from abc_user WHERE matr_user = '{$row_st['agent_recu']}'";
      $agrecu = $pdo->query($sqlagrecu);  
      $row_agrecu = $agrecu->fetch();

      $sqlagtra = "SELECT * from abc_user WHERE matr_user = '{$row_st['agent']}'";
      $agtra = $pdo->query($sqlagtra);  
      $row_agtra = $agtra->fetch();

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(8);	
			$this->Cell(175,8,utf8_decode('Quantité : '.$row_st['qte'].' '.$unite),0,0,'L',true);
			$this->Ln(8);	
			$this->Cell(175,8,utf8_decode('Produit : '.$produit),0,0,'L',true);
			$this->Ln(8);	
			$this->Cell(175,8,utf8_decode('Transféré par : '.$row_agtra['prenom'].' '.$row_agtra['nom'].' à '.date('d/m/Y',strtotime($row_st['date_enreg']))),0,0,'L',true);
			$this->Ln(8);	
			$this->Cell(175,8,utf8_decode('Reçu par : '.$row_agrecu['prenom'].' '.$row_agrecu['nom'].' à '.date('d/m/Y',strtotime($row_st['date_enreg']))),0,0,'L',true);
			$this->Ln(17);
			$this->SetFont('Arial','',12);	
			$this->Cell(100,8,'Signature Recepteur',0,0,'L',true);
			$this->Cell(85,8,utf8_decode('Signature Gest. Stock'),0,0,'R',true);
			$this->Ln(7);
			$this->Ln(7);
 



		}
	






		function footer(){
			 
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