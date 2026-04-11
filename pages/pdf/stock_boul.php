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
			$this->Cell(195,8,utf8_decode('Stock boulangerie'),0,0,'C');
			$this->Ln(7);	
			$this->Cell(195,8,utf8_decode('('.date('d M Y H:i').')'),0,0,'C');
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
			$this->Cell(115,8,utf8_decode('Produits'),0,0,'L',true);
			$this->Cell(73.5,8,utf8_decode('Disponibles'),0,0,'R',true);
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	






      $cat_source = 'boul';
      $statut = 'liv';
      $sql = "SELECT distinct code_produit,cat_source from stock_scr where cat_source = '$cat_source' order by code_stock ASC";
      $req = $pdo->query($sql);  
      while ($row_list_stock = $req->fetch()) {

      $sql_src = "SELECT * from cat_produit_src WHERE cod_prod_src = '{$row_list_stock['code_produit']}'";
      $prod_src = $pdo->query($sql_src);  
      $row_type_prod = $prod_src->fetch();

$four = "1";

      $sqlt = "SELECT sum(qte) AS total_n from stock_scr where code_produit = '{$row_list_stock['code_produit']}' AND code_fourn = '$four'";
      $reqt = $pdo->query($sqlt);  
      $row_nstock = $reqt->fetch();

      $stock_total = $row_nstock['total_n'];




                            if ($row_list_stock['cat_source'] == 'boul') {
      
      $sqlcom = "SELECT sum(qte) AS total_nc from commandes where code_prodvente = '{$row_list_stock['code_produit']}' AND statut = '$statut'";
      $reqcom = $pdo->query($sqlcom);  
      $row_ncom = $reqcom->fetch();


      $sqlpn = "SELECT sum(qte) AS total_nr from panier_scr where cod_prod = '{$row_list_stock['code_produit']}'";
      $reqpn = $pdo->query($sqlpn);  
      $row_npn = $reqpn->fetch();



      $total_nc = $row_ncom['total_nc'] + $row_npn['total_nr']; 

                              if ($total_nc > 0) {
                                $utiliser_exist_c = $total_nc;
                              }else{

                                $utiliser_exist_c = "0";
                              }

      $utiliser = $utiliser_exist_c;


      $disponible = $stock_total - $utiliser;

      $sqlb="SELECT * FROM recettes WHERE cod_rec = '{$row_list_stock['code_produit']}'";
      $reqb=$pdo->query($sqlb);
      $row_rec=$reqb->fetch();

        $typ_pro = $row_rec['nom_rec'];










                            }elseif ($row_list_stock['cat_source'] == 'res') {

      $sqlpn = "SELECT sum(qte) AS total_nr from panier_scr where cod_prod = '{$row_list_stock['code_produit']}'";
      $reqpn = $pdo->query($sqlpn);  
      $row_npn = $reqpn->fetch();

      $sqlcom = "SELECT sum(qte) AS total_nc from commandes where code_prodvente = '{$row_list_stock['code_produit']}' AND statut = '$statut'";
      $reqcom = $pdo->query($sqlcom);  
      $row_ncom = $reqcom->fetch();


      $total_nr = $row_ncom['total_nc'] + $row_npn['total_nr'];

                              if ($total_nr > 0) {
                                $utiliser_exist = $total_nr;
                              }else{

                                $utiliser_exist = "0";
                              }

      $utiliser = $utiliser_exist;

      $disponible = $stock_total - $utiliser;

      $typ_pro = $row_type_prod['nom'];

                            }else{

                            }





                            if ($disponible <= 50) {
                              $dispo_color = '<span style="color:red;">'.$disponible.'</span>';
                            }else{
                              $dispo_color = $disponible;
                            }

                          


			$this->SetFont('Arial','',12);	
			$this->Cell(115,8,utf8_decode($typ_pro),"B",0,'L',true);
			$this->Cell(73.5,8,utf8_decode($disponible),"B",0,'R',true);
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