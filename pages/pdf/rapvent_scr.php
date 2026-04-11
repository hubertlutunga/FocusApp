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




      $sql = "SELECT * from abc_user WHERE identifiant = '{$_SESSION['id']}'";
      $req = $pdo->query($sql);  
      $row_agent = $req->fetch();


			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(195,8,utf8_decode('Rapport du '.date('d/m/Y à H:i')),0,0,'C');
			$this->Ln(7);		
			$this->Cell(195,8,utf8_decode('Caisse: '.$row_agent['prenom'].' '.$row_agent['nom']),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			
		
			$this->Image('../../images/logo_abcrdc_noir.png',8,7,40);
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
			$this->Cell(75,8,'Articles',0,0,'L',true);
			$this->Cell(23,8,utf8_decode('S Reçu'),0,0,'C',true);
			$this->Cell(23,8,utf8_decode('S Vendu'),0,0,'C',true);
			$this->Cell(23,8,utf8_decode('S Dispo'),0,0,'C',true);
			$this->Cell(44,8,utf8_decode('Sommes'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');




      $sqldp = "SELECT DISTINCT cod_prod from panier_scr where YEAR(date_pan) = '$daty' AND Month(date_pan) = '$datm' AND Day(date_pan) = '$datd' AND agent = '{$row_agent['matr_user']}' order by cod_panier ASC";
      $reqdp = $pdo->query($sqldp);  
      while ($row_dp = $reqdp->fetch()) {



      $sqldp_spec = "SELECT prix_u from panier_scr where cod_prod = '{$row_dp['cod_prod']}'";
      $reqdp_spec = $pdo->query($sqldp_spec);  
      $row_dp_spec = $reqdp_spec->fetch();



      $sqls = "SELECT * from menu where cod_rec = '{$row_dp['cod_prod']}'";
      $reqs = $pdo->query($sqls);  
      $row_prod = $reqs->fetch();


      $sqlcompqte = "SELECT sum(qte) AS total_qte from panier_scr where YEAR(date_pan) = '$daty' AND Month(date_pan) = '$datm' AND Day(date_pan) = '$datd' AND cod_prod = '{$row_dp['cod_prod']}' AND agent = '{$row_agent['matr_user']}'";
      $compqte = $pdo->query($sqlcompqte);  
      $row_compqte = $compqte->fetch();

      $sqlcompcout = "SELECT sum(prix_t) AS total_prix from panier_scr where YEAR(date_pan) = '$daty' AND Month(date_pan) = '$datm' AND Day(date_pan) = '$datd' AND cod_prod = '{$row_dp['cod_prod']}' AND agent = '{$row_agent['matr_user']}'";
      $compcout = $pdo->query($sqlcompcout);  
      $row_compcout = $compcout->fetch();





      $ext = "1";

      
      $sqlext = "SELECT * from extension where id_ext = '$ext'";
      $reqext = $pdo->query($sqlext);  
      $row_ext = $reqext->fetch();

//--------------stock recu-----------------------

      $sqlt = "SELECT sum(qte) AS total_n from stock_transfere where cod_produit = '{$row_dp['cod_prod']}' AND id_ext = '$ext' AND id_depar =  '1' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND agent = '{$row_agent['matr_user']}' AND recu = 'oui'";
      $reqt = $pdo->query($sqlt);  
      $row_nstock = $reqt->fetch();

      $rownstock = $row_nstock['total_n'];


      if ($rownstock < 1) {
      	$rownstock = '0';
      }else{
      	$rownstock = $row_nstock['total_n'];
      }

//---------------------stock dispo--------------

      $dispo = $rownstock - $row_compqte['total_qte'];

//------------------------------------------------


			$this->SetFont('Arial','',12);		
			$this->Cell(75,8,utf8_decode($row_prod['nom']),1,0,'L',true);
			$this->Cell(23,8,utf8_decode($rownstock),1,0,'C',true);
			$this->Cell(23,8,utf8_decode($row_compqte['total_qte']),1,0,'C',true);
			$this->Cell(23,8,utf8_decode($dispo),1,0,'C',true);
			$this->Cell(44,8,utf8_decode($row_compcout['total_prix'].' CDF'),1,0,'R',true);
			$this->Ln(7);

      		
      		}
      	
			


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->Ln(15);



			$this->Ln(4);
















      $sqlcompqte_g = "SELECT sum(qte) AS total_qte from panier_scr where YEAR(date_pan) = '$daty' AND Month(date_pan) = '$datm' AND Day(date_pan) = '$datd' AND agent = '{$row_agent['matr_user']}'";
      $compqte_g = $pdo->query($sqlcompqte_g);  
      $row_compqte_g = $compqte_g->fetch();




      $sqltvente = "SELECT COUNT(*) As total_vente from vente_scr WHERE YEAR(date_vente) = '$daty' AND Month(date_vente) = '$datm' AND Day(date_vente) = '$datd' AND agent = '{$row_agent['matr_user']}'";
      $reqtvente = $pdo->query($sqltvente);  
      $row_exist_vente = $reqtvente->fetch();




      $sqlto = "SELECT sum(prix_t) AS total_n from vente_scr where YEAR(date_vente) = '$daty' AND Month(date_vente) = '$datm' AND Day(date_vente) = '$datd' AND agent = '{$row_agent['matr_user']}'";
      $reqto = $pdo->query($sqlto);  
      $row_prixto = $reqto->fetch();




      $sqlscr = "SELECT sum(somme_tva) AS total_sta from vente_scr where YEAR(date_vente) = '$daty' AND Month(date_vente) = '$datm' AND Day(date_vente) = '$datd' AND agent = '{$row_agent['matr_user']}'";
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
			$this->Cell(185,8,utf8_decode('Quantité : '.$row_compqte_g['total_qte']),0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,'Montant Total : '.number_format($row_prixto['total_n'], 0, '.', ' ').' CDF',0,0,'R',true);
			$this->Ln(17);
			$this->SetFont('Arial','',12);	
			$this->Cell(100,8,'Signature Caisse',0,0,'L',true);
			$this->Cell(85,8,utf8_decode('Signature Gérant'),0,0,'R',true);
			$this->Ln(7);
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