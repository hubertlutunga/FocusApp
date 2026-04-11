 	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
       include('../qrscan/phpqrcode/qrlib.php'); 
	



	class myPDF extends FPDF{


		
		function infodf($pdo){



			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);	
			$this->Ln(0);	

		}


		function head_t($pdo){
			
				
			$this->Image('../../images/logo_abcrdc_noir.png',17,5,40);
			$this->Ln(15);	
					
			
			
		}


		function Infofact($pdo){
			
				
      		$ref = $_GET['refer'];
      		$sqlistv = "SELECT * from vente_scr WHERE num_fact = '$ref'";
      		$reqlistv = $pdo->query($sqlistv);  
      		$row_listv = $reqlistv->fetch();
		





      $sql = "SELECT * from abc_user WHERE matr_user = '{$row_listv['agent']}'";
      $req = $pdo->query($sql);  
      $row_agent = $req->fetch();


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->SetMargins(-3,0,0,0);
			$this->Cell(0,0,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(0.1);
			$this->Cell(0,0,mb_convert_encoding('FACTURE N°: '.strtoupper($row_listv['num_fact']), 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,0,mb_convert_encoding('Date: '.date('d/m/Y à H:i',strtotime($row_listv['date_vente'])), 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,0,mb_convert_encoding('Caisse: '.$row_agent['prenom'].' '.$row_agent['nom'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(8);


			$this->SetMargins(0,0,0,0);
			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);	
			$this->Ln(0.1);
			$this->Cell(35,3,'Articles',0,0,'L',true);
			$this->Cell(7,3,mb_convert_encoding('Qte', 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
			$this->Cell(13,3,mb_convert_encoding('PU', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(16,3,mb_convert_encoding('PT', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(6);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);	


      $sqltaux = "SELECT * from taux ";
      $reqtaux = $pdo->query($sqltaux);  
      $row_taux = $reqtaux->fetch(); 



      $sqldp = "SELECT * from panier_scr where num_fact = '{$row_listv['num_fact']}' order by cod_panier ASC";
      $reqdp = $pdo->query($sqldp);  
      while ($row_dp = $reqdp->fetch()) {

      $sqls = "SELECT * from menu where cod_rec = '{$row_dp['cod_prod']}' AND classe = '{$row_dp['classe']}'";
      $reqs = $pdo->query($sqls);  
      $row_prod = $reqs->fetch();
      		

      $cdfpu = $row_dp['prix_u'];
      $cdfpt = $row_dp['prix_t'];
      			
			$this->Cell(35,3,mb_convert_encoding($row_prod['nom'], 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(7,3,mb_convert_encoding($row_dp['qte'], 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
			$this->Cell(13,3,mb_convert_encoding(number_format($cdfpu, 0, '.', ' '), 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(16,3,mb_convert_encoding(number_format($cdfpt, 0, '.', ' '), 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);

      		}


			$this->Ln(6);






      $sqltj = "SELECT * from panier_scr where num_fact = '{$_GET['refer']}' order by cod_panier DESC LIMIT 1";
      $reqtj = $pdo->query($sqltj);  
      $row_tj = $reqtj->fetch();



      $sqltvaf = "SELECT * from tva";
      $reqtvaf = $pdo->query($sqltvaf);  
      $row_tvaf = $reqtvaf->fetch();


      $sqlscr = "SELECT * from vente_scr where num_fact = '{$_GET['refer']}'";
      $reqscr = $pdo->query($sqlscr);  
      $row_vente_scr = $reqscr->fetch();

      if (!is_numeric($row_vente_scr['somme_tva'])) {
      	$somme_tvaxx = "0";
      }else{
      	$somme_tvaxx = $row_vente_scr['somme_tva'];
      }


      $cal_total_santtva = $row_vente_scr['prix_t'] - $somme_tvaxx;
      $cdf_tstva = $cal_total_santtva;

      $total_tva = $somme_tvaxx;
      $cdf_tottva = $total_tva ;

      $total_a_payer = $row_vente_scr['prix_t'];
      $cdf_totapayeer = $total_a_payer;
      $doll_totapayeer = $total_a_payer / $row_tj['taux_jour'];



			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->SetAutoPageBreak(true,0);
			$this->Cell(70,3,'THT (CDF) : '.number_format($cdf_tstva, 0, '.', ' '),0,0,'R',true);
			$this->Ln(5);
			$this->Cell(70,3,'TVA : '.number_format($cdf_tottva, 0, '.', ' '),0,0,'R',true);
			$this->Ln(5);



if ($row_vente_scr['client_fidele'] !== 'x') {

      $sql = "SELECT * from client_restau where code_promo = '{$row_vente_scr['client_fidele']}'";
      $req = $pdo->query($sql);  
      $row_list_client = $req->fetch();


      $sqlmsr = "SELECT sum(prix_t) as total_msr from panier_scr where num_fact = '{$_GET['refer']}'";
      $msr = $pdo->query($sqlmsr);  
      $row_msr = $msr->fetch();
	
      $val_re = $row_msr['total_msr'] * $row_list_client['pourcent'] / 100;

			$this->SetFont('Arial','B',10);	
			$this->Cell(70,3,mb_convert_encoding('Client : '.$row_list_client['prenom'].' '.$row_list_client['nom'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);
	
			$this->SetFont('Arial','B',10);	
			$this->Cell(70,3,mb_convert_encoding('Montant : '.$row_msr['total_msr'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);
	
			$this->SetFont('Arial','B',10);	
			$this->Cell(70,3,mb_convert_encoding('Réduction : '.$row_list_client['pourcent'].'% = '.$val_re, 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);
			
}

			$this->SetFont('Arial','B',10);	
			$this->Cell(70,3,mb_convert_encoding('Total à payer (CDF) : '.number_format($cdf_totapayeer, 0, '.', ' '), 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);

			$this->SetFont('Arial','',10);	
			$this->Cell(70,3,mb_convert_encoding('Taux ($) '.number_format($row_tj['taux_jour'], 0, '.', ' '), 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(5);

			$this->SetFont('Arial','B',10);	
			$this->Cell(70,3,mb_convert_encoding('Dollars ($) : '.number_format($doll_totapayeer, 2, '.', ' '), 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(6);





      if ($row_vente_scr['client_fidele'] !== "x") {
      	 

      $sqlpointt = "SELECT sum(pointcl) AS total_point from vente_scr where client_fidele = '{$row_vente_scr['client_fidele']}'";
      $reqpointt = $pdo->query($sqlpointt);  
      $row_pointt = $reqpointt->fetch();


      $sqlpaie = "SELECT sum(pointcl) AS total_pp from point_payer where code_promo = '{$row_vente_scr['client_fidele']}'";
      $reqpaie = $pdo->query($sqlpaie);  
      $row_paie = $reqpaie->fetch();


      $sql = "SELECT * from client_restau WHERE code_promo = '{$row_vente_scr['client_fidele']}'";
      $req = $pdo->query($sql);  
      $row_info_client = $req->fetch();


       $pdispo = $row_pointt['total_point'] - $row_paie['total_pp'];

			$this->SetFont('Arial','B',10);
			$this->Ln(5);	
			$this->Cell(70,3,mb_convert_encoding($row_info_client['prenom'].' '.$row_info_client['nom'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(4.5);
			$this->SetFont('Arial','',10);	
			$this->Cell(70,3,mb_convert_encoding('Points obtenus : '.$row_vente_scr['pointcl'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(4.5);	
			$this->Cell(70,3,mb_convert_encoding('Total des points : '.$pdispo, 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(7);
      }else{

      }








		}
	


		function fqr($pdo){

			/*

                  $PNG_TEMP_DIR = 'temp/';
                  if (!file_exists($PNG_TEMP_DIR))
                      mkdir($PNG_TEMP_DIR);


                  $codeString = 'https://abcrdc.com/app/adminabc/index.php?page=mb_abc_printfact_scr&ref='.$_GET['refer'].'';

                  $filename = $PNG_TEMP_DIR . 'fp_qr.png';

                  $filename = $PNG_TEMP_DIR . 'fp_qr' . md5($codeString) . '.png';

                  QRcode::png($codeString, $filename);


			$this->Image($PNG_TEMP_DIR . basename($filename),46,3,18);

			*/


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',10);	
			$this->SetMargins(-3,0,0,0,true);
			$this->Cell(0,0,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(8);




                              $req="SELECT * FROM adresse";
                              $ad=$pdo->query($req);
                              $row_ad=$ad->fetch();

                              $req="SELECT * FROM legal";
                              $leg=$pdo->query($req);
                              $row_leg=$leg->fetch();


			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);	
			$this->Cell(0,0,mb_convert_encoding('ABC RDC', 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
			$this->Ln(7);		
			$this->SetMargins(-3,0,0,0);
			$this->SetFont('Arial','',10);		
			$this->Cell(0,0,mb_convert_encoding('IDNAT: '.$row_leg['idnat'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,mb_convert_encoding('RCCM: '.$row_leg['rccm'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(4);		
			$this->Cell(0,0,mb_convert_encoding('N_IMPOT: '.$row_leg['num_impot'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(4);			
			$this->Cell(0,0,mb_convert_encoding('Tél.: '.$row_ad['phone'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(1);			
			$this->Cell(0,2,('_____________________'),0,0,'C');
			$this->Ln(7);	

			$this->Cell(0,0,mb_convert_encoding('*** Merci pour votre passage ***', 'ISO-8859-1', 'UTF-8'),0,0,'C');
		}






	}
	















      $sqlcompp = "SELECT COUNT(*) As total_compp from panier_scr where num_fact = '{$_GET['refer']}'";
      $reqcompp = $pdo->query($sqlcompp);  
      $row_compp = $reqcompp->fetch();

if ($row_compp['total_compp'] <= 1) {

        $taille_page = 80;

}elseif ($row_compp['total_compp'] > 1){
        
        $taille = 55;
        $ligne = 6;
        $taille_page = 100 + $ligne * $row_compp['total_compp'];
}





	$pdf=new myPDF();
	$pdf->AddPage('P', [200,80]);
	//$pdf->AddPage('L','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->fqr($pdo);
	$pdf->Output();
	

?>