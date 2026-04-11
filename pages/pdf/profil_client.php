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



                          

      $daty = date('Y');
      $datm = date('M');
      $datd = date('d');


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
			$this->SetFont('Arial','B',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(195,8,utf8_decode('COMPTE CLIENT'),0,0,'C');
			$this->Ln(7);		
			$this->Cell(195,8,utf8_decode('Date '.date('d M Y')),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	




  
      $matr_cli = $_GET['cod'];
      $sql = "SELECT * from client_boul WHERE matr_cli = '$matr_cli'";
      $req = $pdo->query($sql);  
      $row_info_client = $req->fetch();



      $sqlpexi = "SELECT COUNT(*) As total_pexi from paiement_remise WHERE matr_cli = '$matr_cli'";
      $reqpexi = $pdo->query($sqlpexi);  
      $row_paie_pexi = $reqpexi->fetch();



if ($row_info_client['remise'] <= 0) {
   $remise = '0';
 }else{
  $remise = $row_info_client['remise'];
 } 



    $reqtp="SELECT * FROM typecli_boul where idtype = '{$row_info_client['typecli']}'";
                              $cattp=$pdo->query($reqtp);
                              $row_cattp=$cattp->fetch();

                              if (!$row_cattp['type']) {
                                $typecli = 'Non dérterminé';
                              }else{
                                $typecli = $row_cattp['type'];
                              }

			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(195,8,utf8_decode('Matricule: '.$row_info_client['matr_cli']),0,0,'L');
			$this->Ln(6);		
			$this->Cell(195,8,utf8_decode('Noms: '.$row_info_client['prenom'].' '.$row_info_client['nom']),0,0,'L');
			$this->Ln(6);	
			$this->Cell(195,8,utf8_decode('Remise: '.$remise.' %'),0,0,'L');
			$this->Ln(6);	
			$this->Cell(195,8,utf8_decode('Adresse: '.$row_info_client['adresse']),0,0,'L');
			$this->Ln(6);	
			$this->Cell(195,8,utf8_decode('Téléphone: '.$row_info_client['phone']),0,0,'L');
			$this->Ln(6);	
			$this->Cell(195,8,utf8_decode('Type de compte: '.$typecli),0,0,'L');
			$this->Ln(6);	
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

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',14);
			$this->SetMargins(7,20,0,0,true);
			$this->Cell(200,8,utf8_decode('Total - Commandes'),0,0,'C',true);
			$this->Ln(13);	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(50,8,utf8_decode('Opérations'),0,0,'L',true);
			$this->Cell(50,8,utf8_decode('Ventes'),0,0,'L',true);
			$this->Cell(45,8,utf8_decode('Dette'),0,0,'L',true);
			$this->Cell(43,8,utf8_decode('Remises'),0,0,'L',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	





//-----------------------------calcul credit client ------------------

      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');



      $codcli = $_GET['cod'];
      $sqlcomcred = "SELECT sum(pvt) AS total_ccr from commandes where code_cli = '$codcli' AND typ_paie = 'TR'";
      $comcred = $pdo->query($sqlcomcred);  
      $row_comcred = $comcred->fetch();

      $sqlcalcr = "SELECT sum(pvt) AS total_calcr from commandes where code_cli = '$codcli' AND typ_paie = 'CR'";
      $calcr = $pdo->query($sqlcalcr);  
      $row_calcr = $calcr->fetch();

          $comcred = $row_comcred['total_ccr'] + $row_calcr['total_calcr'];


          $sqlcred = "SELECT sum(montant) as total_cred from dette_paie where matr_client = '$codcli'";
          $cred = $pdo->query($sqlcred);  
          $row_cred = $cred->fetch();

          $credit_client = $row_cred['total_cred']; 

          $rest_client = $comcred - $credit_client;


//----------------------------------------------------------------------


      $sqlrem = "SELECT * from paiement_remise WHERE matr_cli = '$codcli' order by cod_pr DESC LIMIT 1";
      $reqrem = $pdo->query($sqlrem);  
      $row_paie_rem = $reqrem->fetch();

      $date_paierem = $row_paie_rem['date_paie'];
      $date_now = date('Y-m-d').' 23:59:59';





      $sqlc = "SELECT * from commandes  where date_enreg between '$date_paierem' and '$date_now' AND code_cli = '$codcli'";
      $reqc = $pdo->query($sqlc);  
      $row_com = $reqc->fetch();

      $sqls = "SELECT COUNT(*) As total_op from commandes where date_enreg between '$date_paierem' and '$date_now' AND code_cli = '$codcli'";
      $reqs = $pdo->query($sqls);  
      $row_comp_op = $reqs->fetch();

      $req="SELECT * FROM recettes where cod_rec = '{$row_com['code_prodvente']}' ORDER by cod_rec ASC";
      $pv=$pdo->query($req);
      $row_pv=$pv->fetch();


      $sqlts = "SELECT sum(pvt) As total_ts from commandes where date_enreg between '$date_paierem' and '$date_now' AND code_cli = '$codcli'";
      $ts = $pdo->query($sqlts);  
      $row_somts = $ts->fetch();


      $total_somme = $row_somts['total_ts'];


      $sqlsr = "SELECT sum(somme_remise) As total_sr from commandes where date_enreg between '$date_paierem' and '$date_now' AND code_cli = '$codcli'";
      $sr = $pdo->query($sqlsr);  
      $row_somrem = $sr->fetch();


      $somme_remise = $row_somrem['total_sr'];





	
			$this->Cell(50,8,utf8_decode($row_comp_op['total_op']),"B",0,'L',true);
			$this->Cell(50,8,utf8_decode(number_format($total_somme, 0,',',' '). ' CDF'),"B",0,'L',true);
			$this->Cell(45,8,utf8_decode(number_format($rest_client, 0,',',' '). ' CDF'),"B",0,'L',true);
			$this->Cell(43,8,utf8_decode(number_format($somme_remise, 0,',',' '). ' CDF'),"B",0,'L',true);
			$this->Ln(9);

			// $this->Line(50, 45, 210-50, 45);
      			


      	


			$this->Ln(7);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',14);
			$this->SetMargins(7,20,0,0,true);
			$this->Cell(185,8,utf8_decode('Paiements de remise'),0,0,'C',true);
			$this->Ln(13);	

			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(50,8,utf8_decode('Poucentage'),0,0,'L',true);
			$this->Cell(55,8,utf8_decode('Remise'),0,0,'L',true);
			$this->Cell(43,8,utf8_decode('Date'),0,0,'L',true);
			$this->Cell(40,8,utf8_decode('Statut'),0,0,'L',true);
			$this->Ln(10);






			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');


      $sqlx = "SELECT * from paiement_remise WHERE DAY(date_paie) = '$datd' AND Month(date_paie) = '$datm' AND YEAR(date_paie) = '$daty' AND matr_cli = '{$_GET['cod']}' order by cod_pr DESC LIMIT 100";
      $reqx = $pdo->query($sqlx);  
      while ($row_list_paier = $reqx->fetch()) {


      $sqlc = "SELECT * from client_boul WHERE matr_cli = '{$row_list_paier['matr_cli']}'";
      $reqc = $pdo->query($sqlc);  
      $row_info_client = $reqc->fetch();

                             if ($row_list_paier['statut'] == "oui") {
                               $sign = 'Oui';
                             }else{
                               $sign = 'Non';
                             } 

			$this->Cell(50,8,utf8_decode($row_list_paier['pourc_remise']),0,0,'L',true);
			$this->Cell(55,8,utf8_decode($row_list_paier['somme_remise']),0,0,'L',true);
			$this->Cell(43,8,utf8_decode(date('d M Y',strtotime($row_list_paier['date_paie']))),0,0,'L',true);
			$this->Cell(35,8,utf8_decode($sign),0,0,'L',true);
			$this->Ln(9);

     }



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