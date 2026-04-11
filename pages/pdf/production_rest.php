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
			$this->Cell(195,8,utf8_decode('Production Restaurant N°: '.$_GET['ref']),0,0,'C');
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
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      		

	 $statut = 'oui';

	 $classe = "RES";







      $sqlcla = "SELECT count(cod_panier) as total_cla from panier_scr where num_fact = '{$_GET['ref']}' AND classe = '$classe'";
      $reqcla = $pdo->query($sqlcla);  
      $row_cla = $reqcla->fetch();





      $sqldp = "SELECT * from panier_scr where num_fact = '{$_GET['ref']}' AND classe = '$classe'";
      $reqdp = $pdo->query($sqldp);  
      while ($row_dp = $reqdp->fetch()) {



      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_dp['cod_prod']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();


			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);




			$this->Cell(185,8,utf8_decode($row_rec['nom_rec']),0,0,'L',true);
			$this->Ln(10);
      			









      $sqlsu = "SELECT * from stock_utiliser where cod_production = '{$row_dp['num_fact']}' AND cod_rec = '{$row_dp['cod_prod']}' order by cod_su ASC";
      $reqsu = $pdo->query($sqlsu);  
      while ($row_su = $reqsu->fetch()) {


      	$colin = $row_su['cod_ing'];
 		$req="SELECT * FROM ingredien where cod_ing = '$colin'";
 		$ing=$pdo->query($req);
 		$row_ing=$ing->fetch();
  


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

			$this->Cell(40,9,utf8_decode($row_ing['nom_ing']),0,0,'R',true);
			$this->Cell(40,9,utf8_decode(number_format($row_su['su'], 4, '.', ' ')),0,0,'R',true);
			$this->Cell(20,9,utf8_decode($row_ing['unite']),0,0,'L',true);
			$this->Ln(8);

      		
      		}

      		


			$this->Ln(4);
      	}
    






			$this->Ln(10);


/*
			




*/












		}
	








		function infoagent($pdo){
	
//-------------------------recu info facture---------------------


      $sqlinfofact = "SELECT * from panier_scr where num_fact = '{$_GET['ref']}'";
      $infofact = $pdo->query($sqlinfofact);  
      $row_infofact = $infofact->fetch();


//-------------------------------------------------------------

      $sql = "SELECT * from abc_user WHERE matr_user = '{$row_infofact['agent']}'";
      $req = $pdo->query($sql);  
      $agent_f = $req->fetch();		
		
			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

			$this->Cell(40,9,utf8_decode('Caisse: '.$agent_f['prenom'].' '.$agent_f['nom']),0,0,'L',true);
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);					
			
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
	$pdf->infoagent($pdo);
	$pdf->Output();
	

?>