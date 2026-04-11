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
			$this->Cell(270,8,utf8_decode('Les sorties de Stock'),0,0,'C');
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


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	




      $supprimer = "non";

      $statut = "oui";


      $sqlss = "SELECT * from sorti_stockgen where statut = '$statut' AND supprimer = '$supprimer' order by cod_ssg DESC LIMIT 80";
      $reqss = $pdo->query($sqlss);  
      while ($row_list_com = $reqss->fetch()) {


      $sqlsorti = "SELECT * from abc_user WHERE matr_user = '{$row_list_com['agent']}'";
      $reqsorti = $pdo->query($sqlsorti);  
      $agentsorti = $reqsorti->fetch();

        if ($row_list_com['departement'] == "PAT") {

          $dep = "Boulangerie";

        }elseif ($row_list_com['departement'] == "RES") {

          $dep = "Restaurant";
        }




			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->Cell(166,8,utf8_decode('Sortie N°:'.$row_list_com['code_production'].' / '.date('d-M-Y à H:i',strtotime($row_list_com['date_sorti'])).' / '.$dep),0,0,'L',true);
			$this->Cell(100,8,utf8_decode('Par: '.$agentsorti['prenom'].' '.$agentsorti['nom']),0,0,'R',true);
			$this->Ln(12);

 $sqlus = "SELECT * from stock_utiliser where cod_production = '{$row_list_com['code_production']}' order by cod_su DESC";
      $requs = $pdo->query($sqlus);  
      while ($row_us = $requs->fetch()) {


      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_us['cod_rec']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();


        $colin = $row_us['cod_ing'];
        $req="SELECT * FROM ingredien where cod_ing = '$colin'";
        $ing=$pdo->query($req);
        $row_ing=$ing->fetch();

      $sqlmp = "SELECT * from marque_produit where cod_mp = '{$row_us['cod_mp']}'";
      $mp = $pdo->query($sqlmp);  
      $row_momarp = $mp->fetch();

      if (is_null($row_momarp['nom_mp'])) {
        $nomarque = $row_ing['nom_ing'];
      }else{
        $nomarque = $row_ing['nom_ing'].' de '.$row_momarp['nom_mp'];
      }

          if ($row_ing['unite'] == "Qte") {
            $su_et_unite = $row_us['su'];
          }else{
            $su_et_unite = $row_us['su'].' '.$row_ing['unite'];
          }



			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);
			$this->Cell(166,8,utf8_decode('( '.$su_et_unite.' ) '.$nomarque),0,0,'L',true);
			$this->Cell(100,8,utf8_decode($row_rec['nom_rec']),0,0,'R',true);
			$this->Ln(9);

          }

			$this->Ln(4);

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