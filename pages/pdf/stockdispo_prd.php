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
			$this->SetFont('Arial','B',13);
			$this->SetMargins(7,20,0,0,true);	
			$this->Cell(195,8,utf8_decode('Stock'),0,0,'C');
			$this->Ln(7);	
			$this->Cell(195,8,utf8_decode('Du '.date('M Y',strtotime($_GET['da'])).' Au '.date('M Y',strtotime($_GET['db']))),0,0,'C');
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
			$this->Cell(70,8,utf8_decode('Produits'),0,0,'L',true);
			$this->Cell(45,8,utf8_decode('Utilisés'),0,0,'L',true);
			$this->Cell(73.5,8,utf8_decode('Disponibles'),0,0,'L',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	




      		$date_a = $_GET['da'];
      		$date_b = $_GET['db'];

$prix_achat_total = "NULL";
                              
                              $supprimer = "non";
                        

                               $req="SELECT * FROM ingredien where supprimer = '$supprimer' order by cod_ing ASC";
                               $ing=$pdo->query($req);
                               while ($row_ing=$ing->fetch()) {

      $sql = "SELECT * from stock where code_produit = '{$row_ing['cod_ing']}' AND prix_achat_total != '$prix_achat_total'";
      $req = $pdo->query($sql);  
      $row_list_stock = $req->fetch();


      $sqlt = "SELECT sum(poids_total) AS total_n from stock where code_produit = '{$row_list_stock['code_produit']}'";
      $reqt = $pdo->query($sqlt);  
      $row_nstock = $reqt->fetch();








      if (!$row_nstock['total_n']) {
         $rownstock = "0";
      }elseif ($row_nstock['total_n'] < 1) {
         $rownstock = $row_nstock['total_n'];
      }else{
        $rownstock = $row_nstock['total_n'];
      }










$q = 1;

while ($q <= 15) {

$col = "qing".$q++;

      $sorti = "oui";

      $sqlsu = "SELECT sum(su) AS total_nb from stock_utiliser where cod_ing = '{$row_ing['cod_ing']}'  AND date_su between '$date_a' and '$date_b' AND sorti = '$sorti' ";
      $reqsu = $pdo->query($sqlsu);  
      $row_su = $reqsu->fetch();


      $sqlsutout = "SELECT sum(su) AS total_nb from stock_utiliser where cod_ing = '{$row_ing['cod_ing']}' AND date_su between '$date_a' and '$date_b' AND sorti = '$sorti'";
      $reqsutout = $pdo->query($sqlsutout);  
      $row_sutout = $reqsutout->fetch();


        if (!$row_su['total_nb']) {
          $utiliser = '0 '.$row_ing['unite'];
        }else{
          $utiliser = number_format($row_su['total_nb'], 4,',','').' '.$row_ing['unite'];
        }


        $cal_dispo = $rownstock -  $row_sutout['total_nb'];
        $unite = $row_ing['unite'];



        if ($cal_dispo < 0) {
          
          $disponible = number_format($cal_dispo, 4,',',' ').' '.$row_ing['unite'].' (RUPTURE)';
        }else{
          $disponible = number_format($cal_dispo, 4,',','').' '.$unite;
        }


       // $progress = 100 * $row_su['total_nb'] / $rownstock;


 

    }





			$this->SetFont('Arial','',12);	
			$this->Cell(70,8,utf8_decode($row_ing['nom_ing']),"B",0,'L',true);
			$this->Cell(45,8,utf8_decode($utiliser),"B",0,'L',true);
			$this->Cell(73.5,8,utf8_decode($disponible),"B",0,'L',true);
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
	$pdf->AddPage('P','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>