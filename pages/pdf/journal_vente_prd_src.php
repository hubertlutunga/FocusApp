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
			$this->Cell(195,8,utf8_decode('Journal des ventes allant du '.date('d M Y',strtotime($_GET['da'])).' au '.date('d M Y',strtotime($_GET['db']))),0,0,'C');
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
			$this->Cell(70,8,'Article',0,0,'L',true);
			$this->Cell(15,8,utf8_decode('Qte'),0,0,'C',true);
			$this->Cell(50,8,utf8_decode('PU'),0,0,'R',true);
			$this->Cell(50,8,utf8_decode('PT'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      
      		$da = $_GET['da'].' 00:00:00';
      		$db = $_GET['db'].' 23:59:59';

      $sqlistv = "SELECT * from vente_scr WHERE date_vente between '$da' and '$db' AND agent = '{$row_agent['matr_user']}' order by cod_vente DESC";
      $reqlistv = $pdo->query($sqlistv);  
      while ($row_listv = $reqlistv->fetch()) {


			$this->SetFont('Arial','B',12);	
			$this->Cell(185,8,utf8_decode('N° '.$row_listv['num_fact']. ' ('.date('d M Y',strtotime($row_listv['date_vente'])).')'),1,0,'L',true);
			$this->Ln(7);
      			


      $sqldp = "SELECT * from panier_scr where num_fact = '{$row_listv['num_fact']}' order by cod_panier ASC";
      $reqdp = $pdo->query($sqldp);  
      while ($row_dp = $reqdp->fetch()) {

      $sqls = "SELECT * from menu where cod_rec = '{$row_dp['cod_prod']}'";
      $reqs = $pdo->query($sqls);  
      $row_prod = $reqs->fetch();

                            if ($row_dp['tva'] == "oui") {
                              $aj = "+";
                            }else{
                              $aj = "-";
                            }



			$this->SetFont('Arial','',12);	
			$this->Cell(70,8,utf8_decode($row_prod['nom'].' ('.$aj.')'),1,0,'L',true);
			$this->Cell(15,8,utf8_decode($row_dp['qte']),1,0,'C',true);
			$this->Cell(50,8,utf8_decode(number_format($row_dp['prix_u'], 0, '.', ' ').' CDF'),1,0,'R',true);
			$this->Cell(50,8,utf8_decode(number_format($row_dp['prix_t'], 0, '.', ' ').' CDF'),1,0,'R',true);
			$this->Ln(7);

      		
      		}
      	
			
			$this->Cell(185,8,utf8_decode('+ TVA ('.$row_tvaf['pourc_tva'].'%)'),1,0,'R',true);
			$this->Ln(7);
			$this->Cell(135,8,utf8_decode(''),1,0,'R',true);
			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->Cell(50,8,utf8_decode(number_format($row_listv['prix_t'], 0, '.', ' ').' CDF'),1,0,'R',true);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->Ln(15);
      	}


			$this->Ln(4);




















      $sqltvente = "SELECT COUNT(*) As total_vente from vente_scr WHERE date_vente between '$da' and '$db' AND agent = '{$row_agent['matr_user']}' ";
      $reqtvente = $pdo->query($sqltvente);  
      $row_exist_vente = $reqtvente->fetch();


      $sqlto = "SELECT sum(prix_t) AS total_n from vente_scr  WHERE date_vente between '$da' and '$db' AND agent = '{$row_agent['matr_user']}'";
      $reqto = $pdo->query($sqlto);  
      $row_prixto = $reqto->fetch();




      $sqlscr = "SELECT sum(somme_tva) AS total_sta from vente_scr  WHERE date_vente between '$da' and '$db' AND agent = '{$row_agent['matr_user']}'";
      $reqscr = $pdo->query($sqlscr);  
      $row_vente_scr = $reqscr->fetch();




      $rec_tva = $row_vente_scr['total_sta'];
      
      $cal_total_santtva = $row_prixto['total_n'] - $rec_tva;

      $total_tva = $rec_tva;

      $total_a_payer = $cal_total_santtva + $total_tva;






			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	
			$this->Cell(185,8,'Nombre des ventes : '.$row_exist_vente['total_vente'],0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,'Montant total des ventes : '.number_format($row_prixto['total_n'], 0, '.', ' ').' CDF',0,0,'R',true);
			$this->Ln(7);
			$this->Cell(185,8,'Hors taxes : '.number_format($cal_total_santtva, 0, '.', ' ').' CDF',0,0,'R',true);
			$this->Ln(7);
			$this->Cell(185,8,'TVA ('.$row_tvaf['pourc_tva'].'%) : '.number_format($rec_tva, 0, '.', ' ').' CDF',0,0,'R',true);
			$this->Ln(7);



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