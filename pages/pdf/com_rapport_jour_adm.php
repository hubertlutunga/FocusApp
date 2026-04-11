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




      $sqlxc = "SELECT * from abc_user WHERE identifiant = '{$_SESSION['id']}'";
      $reqxc = $pdo->query($sqlxc);  
      $row_agentxc = $reqxc->fetch();



                          

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
			$this->Cell(195,8,utf8_decode('Rapport de Commandes des Abonnés'),0,0,'C');
			$this->Ln(7);		

			$this->Cell(195,8,'Mois de '.date('M Y'),0,0,'C');
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


      $agent = $row_agent['matr_user'];

			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,utf8_decode(''),0,0,'L');
			$this->Cell(150,15,utf8_decode(''),0,0,'L');
			$this->Ln(0);	
			$this->Cell(45,15,utf8_decode('Produits'),1,0,'C',true);
			$this->Cell(30,15,utf8_decode('Reception'),1,0,'C',true);
			$this->Cell(28,15,utf8_decode('Nb Vendu'),1,0,'C',true);
			$this->Cell(20,15,utf8_decode('Rtrné'),1,0,'C',true);
			$this->Cell(35,15,utf8_decode('Créd/Somme'),1,0,'C',true);
			$this->Cell(30,15,utf8_decode('Total vendu'),1,0,'C',true);
			$this->Ln(15);




			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	



      $daty = date('Y');
      $datm = date('m');
      $datd = date('d');





      $sql = "SELECT distinct code_prodvente from commandes where typecli != '4' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' order by code_com ASC";
      $req = $pdo->query($sql);  
      while ($row_list_com = $req->fetch()) {



      $sqls="SELECT * FROM recettes where cod_rec = '{$row_list_com['code_prodvente']}'";
      $reqs=$pdo->query($sqls);
      $row_nom_prod = $reqs->fetch();




			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	
			$this->Cell(188,10,utf8_decode($row_nom_prod['nom_rec']),1,0,'L',true);
			$this->Ln(10);




//--------------stock recu-----------------------

      $sqlt = "SELECT sum(qte) AS total_n from stock_transfere where cod_produit = '{$row_list_com['code_prodvente']}' AND id_depar =  '2' AND  YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND recu = 'oui'";
      $reqt = $pdo->query($sqlt);  
      $row_nstock = $reqt->fetch();

      $rownstock = $row_nstock['total_n'];


      if ($rownstock < 1) {
      	$rownstock = '0';
      }else{
      	$rownstock = $row_nstock['total_n'];
      }



//------------------------------------------------

//--------------nombre des commande produit-----------------------

      $sqlqtecom = "SELECT sum(qte) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli != 'GROSS' AND code_cli != 'NULL'";
      $qtecom = $pdo->query($sqlqtecom);  
      $row_qtecom = $qtecom->fetch();

      $rowqtecom = $row_qtecom['total_n'];

      if ($rowqtecom < 1) {
      	$rowqtecom = '0';
      }else{
      	$rowqtecom = $row_qtecom['total_n'];
      }

      //--------------- sommes command ------------

      $sqlmont_comt = "SELECT sum(pvt) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli != 'GROSS' AND code_cli != 'NULL'";
      $reqmont_comt = $pdo->query($sqlmont_comt);  
      $row_mont_comt = $reqmont_comt->fetch();

      $rowmontcomt = $row_mont_comt['total_n'];

      if ($rowmontcomt < 1) {
      	$rowmontcomt = '0';
      }else{
      	$rowmontcomt = $row_mont_comt['total_n'];
      }

//------------------------------------------------



//--------------nombre des commande produit GROSS-----------------------

      $sqlqtecomgross = "SELECT sum(qte) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli = 'GROSS'";
      $qtecomgross = $pdo->query($sqlqtecomgross);  
      $row_qtecomgross = $qtecomgross->fetch();

      $rowqtecomgross = $row_qtecomgross['total_n'];

      if ($rowqtecomgross < 1) {
      	$rowqtecomgross = '0';
      }else{
      	$rowqtecomgross = $row_qtecomgross['total_n'];
      }


      //--------------- sommes command grossiste------

      $sqlmont_gross = "SELECT sum(pvt) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli = 'GROSS'";
      $reqmont_gross = $pdo->query($sqlmont_gross);  
      $row_mont_gross = $reqmont_gross->fetch();

      $rowmontgross = $row_mont_gross['total_n'];

      if ($rowmontgross < 1) {
      	$rowmontgross = '0';
      }else{
      	$rowmontgross = $row_mont_gross['total_n'];
      }

//------------------------------------------------

//--------------nombre des commande produit passant-----------------------

      $sqlqtecompassant = "SELECT sum(qte) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli = 'NULL'";
      $qtecompassant = $pdo->query($sqlqtecompassant);  
      $row_qtecompassant = $qtecompassant->fetch();

      $rowqtecompassant = $row_qtecompassant['total_n'];

      if ($rowqtecompassant < 1) {
      	$rowqtecompassant = '0';
      }else{
      	$rowqtecompassant = $row_qtecompassant['total_n'];
      }




      //--------------- sommes command passant ------

      $sqlmont_pass = "SELECT sum(pvt) AS total_n from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND code_cli = 'NULL'";
      $reqmont_pass = $pdo->query($sqlmont_pass);  
      $row_mont_pass = $reqmont_pass->fetch();

      $rowmontpass = $row_mont_pass['total_n'];

      if ($rowmontpass < 1) {
      	$rowmontpass = '0';
      }else{
      	$rowmontpass = $row_mont_pass['total_n'];
      }




//----------------total retourné------------------

		$retouner = $rownstock - $rowqtecom - $rowqtecomgross - $rowqtecompassant;

//----------------somme total tout------------------

		$somme_total = $rowmontcomt + $rowmontgross + $rowmontpass;

//-------------------------------------------------





//--------------------------------calucule dette-----------------


      $sqlcomcred = "SELECT sum(pvt) AS total_ccr from commandes where typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND typ_paie = 'TR'";
      $comcred = $pdo->query($sqlcomcred);  
      $row_comcred = $comcred->fetch();

      $sqlcalcr = "SELECT sum(pvt) AS total_calcr from commandes where  typecli != '4' AND code_prodvente = '{$row_list_com['code_prodvente']}' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND typ_paie = 'CR'";  
      $calcr = $pdo->query($sqlcalcr);  
      $row_calcr = $calcr->fetch();

          $comcred = $row_comcred['total_ccr'] + $row_calcr['total_calcr'];



          $sqlcred = "SELECT sum(montant) as total_cred from dette_paie where   YEAR(date_ce) = '$daty' AND Month(date_ce) = '$datm'";
          $cred = $pdo->query($sqlcred);  
          $row_cred = $cred->fetch();

          $credit_client = $row_cred['total_cred']; 

          $rest_client = $comcred - $credit_client;

          if ($rest_client < 1) {
          	$rest_client = 0;
          }else{
          	$rest_client = $rest_client;
          }



//-----------------------------------------------------------------------





			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->SetMargins(11,7,0,0,true);


			$this->Cell(45,10,utf8_decode('Abonnés'),1,0,'L',true);
$this->Cell(30,10,' ','LTR',0,'L',0);   // empty cell with left,top, and right borders
			$this->Cell(28,10,utf8_decode($rowqtecom),1,0,'C',true);
$this->Cell(20,10,'','LTR',0,'L',0);
			$this->Cell(35,10,utf8_decode($rest_client.' / '.$rowmontcomt),1,0,'C',true);

$this->Cell(30,10,'','LTR',0,'L',0);

                $this->Ln(10);

			$this->Cell(45,10,utf8_decode('Grossiste (-25%)'),1,0,'L',true);
$this->Cell(30,10,$rownstock,'LR',0,'C',0);  // cell with left and right borders
			$this->Cell(28,10,utf8_decode($rowqtecomgross),1,0,'C',true);
$this->Cell(20,10,$retouner,'LR',0,'C',0);
			$this->Cell(35,10,utf8_decode($rowmontgross),1,0,'C',true);
$this->Cell(30,10,$somme_total,'LR',0,'C',0);

                $this->Ln(10);

			$this->Cell(45,10,utf8_decode('Détail'),1,0,'L',true);
$this->Cell(30,10,'','LBR',0,'L',0);   // empty cell with left,bottom, and right borders
			$this->Cell(28,10,utf8_decode($rowqtecompassant),1,0,'C',true);
$this->Cell(20,10,'','LRB',0,'L',0);
			$this->Cell(35,10,utf8_decode($rowmontpass),1,0,'C',true);
$this->Cell(30,10,'','LRB',0,'L',0);




			$this->Ln();




      	}


			$this->Ln(4);










































			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->Ln(15);



			$this->Ln(4);







//--------------------------------calcule total nombre op-----------------


      $sqlcomttid = "SELECT count(code_com) AS total_id from commandes where typecli != '4' AND  YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm'";
      $comttid = $pdo->query($sqlcomttid);  
      $row_comttid = $comttid->fetch();

//--------------------------------calcule total qte com-----------------

      $sqlcomtt = "SELECT sum(qte) AS total_com from commandes where typecli != '4' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm'";
      $comtt = $pdo->query($sqlcomtt);  
      $row_comtt = $comtt->fetch();

//--------------------------------calcule total somme com-----------------

      $sqlcomsom = "SELECT sum(pvt) AS total_com from commandes where typecli != '4' AND YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm'";
      $comsom = $pdo->query($sqlcomsom);  
      $row_comsom = $comsom->fetch();






//--------------------------------calucule dette-----------------


      $sqlcomcredtt = "SELECT sum(pvt) AS total_ccr from commandes where typecli != '4' AND   YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND typ_paie = 'TR'";
      $comcredtt = $pdo->query($sqlcomcredtt);  
      $row_comcredtt = $comcredtt->fetch();

      $sqlcalcrtt = "SELECT sum(pvt) AS total_calcr from commandes where typecli != '4' AND   YEAR(date_enreg) = '$daty' AND Month(date_enreg) = '$datm' AND typ_paie = 'CR'";  
      $calcrtt = $pdo->query($sqlcalcrtt);  
      $row_calcrtt = $calcrtt->fetch();

          $comcredtt = $row_comcredtt['total_ccr'] + $row_calcrtt['total_calcr'];







          $sqlcredtt = "SELECT sum(montant) as total_cred from dette_paie where  YEAR(date_ce) = '$daty' AND Month(date_ce) = '$datm'";
          $credtt = $pdo->query($sqlcredtt);  
          $row_credtt = $credtt->fetch();

          $credit_clienttt = $row_credtt['total_cred']; 

          $rest_clienttt = $comcredtt - $credit_clienttt;

          if ($rest_clienttt < 1) {
          	$rest_clienttt = 0;
          }else{
          	$rest_clienttt = $rest_clienttt;
          }



//----------------------- payé ---------------------------------------

          if ($credit_clienttt < 1) {
          	$payer = 0;
          }else{
          	$payer = $credit_clienttt;
          }







			$this->SetFillColor(255, 255, 255, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	
			$this->Cell(185,8,utf8_decode('Opérations : ').$row_comttid['total_id'],0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,utf8_decode('Stock vendu: '.$row_comtt['total_com']),0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,utf8_decode('Commande crédit : '.$comcredtt.' CDF'),0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,utf8_decode('Tranche (crédit) payée : '.$payer.' CDF'),0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,utf8_decode('Reste à payer : '.$rest_clienttt.' CDF'),0,0,'R',true);
			$this->Ln(7);	
			$this->Cell(185,8,'Total somme: '.number_format($row_comsom['total_com'], 0, '.', ' ').' CDF',0,0,'R',true);
			$this->Ln(17);
			$this->Ln(7);





/*



$this->Cell(40,5,' ','LTR',0,'L',0);   // empty cell with left,top, and right borders
$this->Cell(50,5,'111 Here',1,0,'L',0);
$this->Cell(50,5,'222 Here',1,0,'L',0);

                $this->Ln();

$this->Cell(40,5,'Solid Here','LR',0,'C',0);  // cell with left and right borders
$this->Cell(50,5,'[ o ] che1','LR',0,'L',0);
$this->Cell(50,5,'[ x ] che2','LR',0,'L',0);

                $this->Ln();

$this->Cell(40,5,'','LBR',0,'L',0);   // empty cell with left,bottom, and right borders
$this->Cell(50,5,'[ x ] def3','LRB',0,'L',0);
$this->Cell(50,5,'[ o ] def4','LRB',0,'L',0);

                $this->Ln();
                $this->Ln();
                $this->Ln();


                


                */




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