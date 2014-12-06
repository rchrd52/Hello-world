// pay.php for webshops //

session_start();
        include ("db_config/db_config_backofficepro.php");
        include ("functies/functie.php");
        
            winkelnummer();

        
            $klant_id = $_SESSION['klant_id'];
            $emailklant = $_SESSION['email'];
            $orderid = $_POST['orderid'];
            $amount1 = $_SESSION['amount'];
                         //echo "post betalen ".$_POST['betalen']." en amount is ".$amount1." en order ".$_POST['orderid'];
        $webpass = "SELECT * FROM `webpass` WHERE `winkelnummer` = '$winkelnummer'";
        $webpassres = mysql_query($webpass);
        $webpassrij = mysql_fetch_array($webpassres);
            
            // =================================================================================================================================
            $transactiecode = str_pad($klant_id, 5, "0", STR_PAD_LEFT).str_pad($orderid, 5, "0", STR_PAD_LEFT);
            // =================================================================================================================================
             // ***** Verwerken van de order in kroon_order *****
            
            $refklant1 = $_POST['referentie'];
            if ($refklant1 == ""){
                $refklant = "-----";
            } else {
                $refklant = $_POST['referentie'];
            }
            $medklant = $_POST['mededeling'];
            // ***** even een controle overzicht van variabelen *****

            //echo "orderid is: ".$_POST['orderid']." <br>";
            //echo "amount is: ".$amount1." <br>";
            //echo "klant is: ".$klant_id." <br>";
            //echo "transactie is: ".$transactiecode." <br>";
            
            // ***** gegevens ophalen t.b.v. orderwerwerking *****
            

            // ***** eerst klantgegevens *****
            
            $klantgegevens = "SELECT * FROM `klant_overzicht` WHERE `Code` = '$klant_id'";
            $klantgegevensres = mysql_query($klantgegevens);
            $klantgegevensrij = mysql_fetch_array($klantgegevensres);
            $klantemail2 = "SELECT * FROM `klant_contactpersoon` WHERE `klant_id` = '$klant_id'";
            $klantemailres = mysql_query($klantemail2);
            while ($klantemailrij = mysql_fetch_array($klantemailres)) {
                if ($klantemailrij['email'] != "") {
                    $klantemail = $klantemailrij['email'];        
                } 
            }
            
            
            $klantnaam = $klantgegevensrij['Name'];
            $klantadres = $klantgegevensrij['ContactAddressLine1'];
            $klanthuisnummer = $klantgegevensrij['huisnummer'];
            $klantpostcode = $klantgegevensrij['ContactPostalCode'];
            $klantplaats = $klantgegevensrij['ContactCity'];
            
            $facturatieper = $klantgegevensrij['emailfactuur'];
            $klantenkorting = $klantgegevensrij['Korting'];
            
            // ***** eerst klantgegevens *****
            
            // ***** order gegevens *****
            $ordergegevens = "SELECT * FROM `kroon_order` WHERE `id` = '$orderid' AND `winkelnummer` = '$winkelnummer'";
            $ordergegevensres = mysql_query($ordergegevens);
            $ordergegevensrij = mysql_fetch_array($ordergegevensres);
             
            
            // ***** verzendadres *****
            $verzadres = "SELECT * FROM `klant_verzendadres` WHERE `id` = '$ordergegevensrij[verzendid]'";
            $verzadresres = mysql_query($verzadres);
            $verzadresrij = mysql_fetch_array($verzadresres);
            
            // ***** contactpersoon *****
            $contpers = "SELECT * FROM `klant_contactpersoon` WHERE `id` = '$ordergegevensrij[contactpersoonid]'";
            $contpersres = mysql_query($contpers);
            $contpersrij = mysql_fetch_array($contpersres);
            
            
            // ***** order gegevens *****
            
            // ***** rest gegevens *****
            $amountgoed1 = str_replace(',', '.', $amount1);
            $amountgoed2 = number_format($amountgoed1,2);
            $amount = $amountgoed2 * 100 ;
           if ($_POST['afrekenen'] == "direct") {
            
     $regels = "SELECT * FROM `orderline` WHERE `order_id` = '$orderid' AND `winkelnummer` = '$winkelnummer'";
     $regelsres = mysql_query($regels);
     while ($regelsrij = mysql_fetch_array($regelsres)) {
        $lines = $lines . "<tr><td>".$regelsrij['itemcode2']." </td><td>".$regelsrij['omschrijving']." </td><td>".$regelsrij['number']." </td><td> &euro; ".$regelsrij['prijs']."</td></tr>";    
     }       
            
            // ***** rest gegevens *****
            
    //change this to your email.
    $to = $emailklant;
    $from = $webpassrij['emailalgemeen'];
    $subject = "Naam nog invullen : Orderbevestiging " . $orderid;
		
        $betaaltermijn = "SELECT * FROM `klant_betaaltermijn` WHERE `id` = '$klantgegevensrij[betaaltermijn]'";
        $betaaltermijnres = mysql_query($betaaltermijn);
        while ($betaaltermijnrij = mysql_fetch_array($betaaltermijnres)) {
        if ($betaaltermijnrij['betaaltermijn'] > 0){
           $betopm = "Uw krediettermijn"; 
           $termijn = $betaaltermijnrij['betaaltermijn'] . " dagen netto";
           $mailtermijn = "Uw krediettermijn: " . $betaaltermijnrij['betaaltermijn'] . " dagen netto";
        } else {
           $betopm = "Uw krediettermijn"; 
           $termijn = "U dient vooruit te betalen";
	   $mailtermijn = "Betaling: Via iDeal";
        }
        }
        if ($facturatieper > 0){
            $factper = "E-mail";
        } else {
            $factper = "Post";
        }
        if ($klantenkorting > 0){
            $klkrting = "<tr><td width='175'>Gehanteerde klantenkorting</td><td width='175' align='left'>: " . $klantenkorting . "%</td></tr>";
	    $klmailkorting = "Gehanteerde klantenkorting " . $klantenkorting . "%";
        } else {
            $klkrting = "";
            $klmailkorting = "";
        }
        $totaalbestelling = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]'";
        $totaalbestellingres = mysql_query($totaalbestelling);
        while ($totaalbestellingrij = mysql_fetch_array($totaalbestellingres)) {
        $totaalbestellingtot = $totaalbestellingrij['SUM(prijs)']; 
        }
        $totaalhoog = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `vat` = '3'";
        $totaalhoogres = mysql_query($totaalhoog);
        while ($totaalhoogrij = mysql_fetch_array($totaalhoogres)) {
        $totaalhoogbtw = $totaalhoogrij['SUM(prijs)']; 
        }
        $totaallaag = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `vat` = '2'";
        $totaallaagres = mysql_query($totaallaag);
        while ($totaallaagrij = mysql_fetch_array($totaallaagres)) {
        $totaallaagbtw = $totaallaagrij['SUM(prijs)']; 
        }
        $btwdef = "SELECT * FROM `btw`";
        $btwdefres = mysql_query($btwdef);
        while ($btwdefrij = mysql_fetch_array($btwdefres)) {
        if ($btwdefrij['id'] == 2){
        $btwlaagtotaal = $totaallaagbtw;
        }
        if ($btwdefrij['id'] == 3){
        $btwhoogtotaal = $totaalhoogbtw;    
        }
        }
        $orderregels = "SELECT * FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `winkelnummer` = '$winkelnummer'";
        $orderregelsres = mysql_query($orderregels);
        while ($orderregelsrij = mysql_fetch_array($orderregelsres)) {
        
        $artgeg = "SELECT * FROM `artikel_overzicht` WHERE `id` = '$orderregelsrij[itemcode2]'";
        $artgegres = mysql_query($artgeg);
        while ($artgegrij = mysql_fetch_array($artgegres)) {
        $omschr = $artgegrij['titelsite'];
            }
	}
	if ($medklant != ""){
                $mededel = "<font size='3' color='000080'>" . $medklant . "</font>";
            } else {
                $mededel = "<font size='3'>U heeft geen mededeling meegestuurd met deze order</font>";
            }
    // opbouw gegevens uit database 
    // Orderregels eerst
	$orderlineMB = "SELECT * FROM `orderline` WHERE `order_id` = '$orderid'";
 	$orderlineMBres = mysql_query($orderlineMB);
	while ($orderlineMBrij = mysql_fetch_array($orderlineMBres)) {
    
	$orderregels = '
	<tr><td colspan="8"><img src="http://'.$webpassrij['websitenaam'].'/afb/nieuwsbrief/.jpg"></td></tr>
	<tr>
	<td>&nbsp;</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">'. $orderlineMBrij['number'] .'</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">'. strtoupper($orderlineMBrij['omschrijving']) .'</td>
	<td>&nbsp;</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt"><a href="http://' .$webpassrij['websitenaam']. '/artikel_details.php?zoek='.$orderlineMBrij['itemcode2'].'">'. $orderlineMBrij['itemcode2'] .'</a></td>
	<td align="right"><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">&euro; '. number_format(($orderlineMBrij['prijs']/$orderlineMBrij['number']), 2, ',', '') .'</td>
	<td align="right"><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">&euro; '. number_format($orderlineMBrij['prijs'], 2, ',', '') .'</td>
	<td>&nbsp;</td>
	</tr>';
	
	$orderregels2 = $orderregels2.$orderregels;

} 
    
    
    //begin of HTML message
    $message = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
</head>
<body leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0' style='-webkit-text-size-adjust: none;background-color: #C3C3C3;margin: 0px 0px 0px 0px;padding: 0px 0px 0px 0px;width: 100%;'>
<?php
tagmanager()
?>
<table id='backgroundTable' height='100%'' width='100%' border='0' cellpadding='0' cellspacing='0' style='height: 100%;width: 100%;margin: 0px 0px 0px 0px;padding: 0px 0px 0px 0px;''>
<tr>
<td align='center' valign='top'>
<table width='644' border='0' cellpadding='0' cellspacing='0'>
<tr><td height='10'>&nbsp;</td></tr>
<tr><td><a href='http://" . $webpassrij['websitenaam'] . "'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/.jpg' border='0' alt='Header nieuwsbrief'></a></td></tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
U heeft zojuist een bestelling geplaatst bij " . $webpassrij['configuratienaam'] . ". Hieronder treft u de gegevens aan zoals u deze aan ons heeft doorgegeven.<br><br>
<td width='10'>&nbsp;</td>
</td></tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='300'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Factuuradres</b><br><br>
" . $klantnaam . "<br>
T.a.v.: Crediteurenadministratie<br>
" . $klantadres . " " . $klanthuisnummer . "<br>
" . $klantpostcode . " " . $klantplaats . "<br>
</span></td>
<td width='24'>&nbsp;</td>
<td width='300'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Verzendadres</b><br><br>
" . $verzadresrij['delnaam'] . "<br>
T.a.v.: " . $verzadresrij['tav'] . "<br>
" . $verzadresrij['deladres1'] . " " . $verzadresrij['huisnummer'] . "<br>
" . $verzadresrij['delpostcode'] . " " . $verzadresrij['delplaats'] . "<br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr></table>                                                                
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='300' valign='top'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Klantgegevens</b><br><br>
Uw klantnummer: " . $ordergegevensrij['klant_code'] . "<br>
Ons ordernummer: " . $ordergegevensrij['id'] . "<br>
Uw ordernummer: " . $refklant . "<br>
</span></td>
<td width='24'>&nbsp;</td>
<td width='300' valign='top'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Factuurgegevens</b><br><br>
". $mailtermijn . "<br>
Facturatie per: " . $factper . "<br>
" . $klmailkorting . "<br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr></table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Uw ordergegevens</b><br><br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='60'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Aantal</b></span></td>
<td width='314'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Omschrijving</b></span></td>
<td width='20'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
&nbsp;</span></td>
<td width='60'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Art.nr.</b></span></td>
<td width='85' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Prijs p/st</b></span></td>
<td width='85' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Totaal</b></span></td>
<td width='10'>&nbsp;</td>
</tr>" . $orderregels2 ."
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr><td colspan='4' height='20'></td>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>Totaalbedrag exclusief BTW :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($totaalbestellingtot, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>Verzendkosten exclusief BTW :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($ordergegevensrij['verzendkosten'], 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>BTW hoog tarief over &euro; " . number_format(($totaalhoogbtw + $ordergegevensrij['verzendkosten']), 2, ',', '.') . " :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($btwhoogtotaal, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>BTW laag tarief over &euro; " . number_format(($totaallaagbtw), 2, ',', '.') . " :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($btwlaagtotaal, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr><td colspan='4' height='20'></td>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'><b>Totaalbedrag inclusief BTW :</b></span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'><b>&euro; " . number_format($ordergegevensrij['amount'], 2, ',', '.') . "</b></span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<br><b>Uw mededeling aan ons</b>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>" .$mededel  . "</span></td>
<td width='10'>&nbsp;</td>
</tr>

</table>

<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<br><br>
<b>Overige informatie</b><br>
<img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'><br>
Voor onze Algemene Voorwaarden, leveringsvoorwaarden en dergelijke verwijzen wij naar onze website. Zodra u ingelogd bent, kunt u tevens o.a. uw orderhistorie, facturen en spaarpunten inzien via het onderdeel 'Mijn pagina'.<br><br>Heeft u vragen en/of opmerkingen betreffende deze orderbevestiging, dan horen wij deze graag van u.

<br><br>
Met vriendelijke groet,<br><br><br>
" . $webpassrij['configuratienaam'] . "<br>
" . $webpassrij['straat'] . "  " . $webpassrij['huisnummer'] . " <br>
" . $webpassrij['postcode'] . " " . $webpassrij['plaats'] . " <br>
T. : " . $webpassrij['telefoon'] . "<br>
E. : " . $webpassrij['emailalgemeen'] . "
</span>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0'>
<tr><td><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/.jpg' border='0' alt='End of nieuwsbrief'></td></tr>
</table>
</td>
</tr>
</table>
</body>
</html>
";
   //end of message
    $headers  = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    //options to send to cc+bcc
    //$headers .= "Cc: [email]info@kroonleveranciers.nl[/email]";
    $headers .= "Bcc: info@kroonleveranciers.nl";
    
    // now lets send the email.
    mail($to, $subject, $message, $headers);

    echo "<br><br>Message has been sent now....!";
            
     	$datasave = "SELECT * FROM `kroon_order` WHERE `id` = '$orderid'";
     	$datasaveres = mysql_query($datasave);
     	while ($datasaverij = mysql_fetch_array($datasaveres)) {
	
	$saveverzend = "SELECT * FROM `klant_verzendadres` WHERE `id` = '$datasaverij[verzendid]'";
     	$saveverzendres = mysql_query($saveverzend);
     	while ($saveverzendrij = mysql_fetch_array($saveverzendres)) {
	$delnaamMB = $saveverzendrij['delnaam'];
	$tavMB = $saveverzendrij['tav'];
	$deladres1MB = $saveverzendrij['deladres1'];
	$huisnummerMB = $saveverzendrij['huisnummer'];
	$delpostcodeMB = $saveverzendrij['delpostcode'];
	$delplaatsMB = $saveverzendrij['delplaats'];
	$delcountryMB = $saveverzendrij['delcountry'];

	}
	}
            
            // ***** Database status wijzigen in besteld, zodat het winkelmandje weer leeg is *****
            $status = "UPDATE `kroon_order` SET 
		`status` = 'ideal',
		`transactie` = '$transactiecode',
		`delnaam` = '$delnaamMB',
		`deladres1` = '$deladres1MB',
		`tav` = '$tavMB',
		`huisnummer` = '$huisnummerMB',
		`delpostcode` = '$delpostcodeMB',
		`delplaats` = '$delplaatsMB',
		`delcountry` = '$delcountryMB',
		`referentie` = '$_POST[referentie]',
		`tekstregel` = '$_POST[mededeling]'
	    WHERE `id` = '$orderid'";
            $statusres = mysql_query($status);
            
            
            // ***** Database status wijzigen in besteld, zodat het winkelmandje weer leeg is *****
            
			include('MultiSafepay.class.php');
			include('MultiSafepay.config.php');

			$msp = new MultiSafepay();


			$msp->test                         = MSP_TEST_API;
			$msp->merchant['account_id']       = MSP_ACCOUNT_ID;
			$msp->merchant['site_id']          = MSP_SITE_ID;
			$msp->merchant['site_code']        = MSP_SITE_CODE;
			$msp->merchant['notification_url'] = BASE_URL . '../notify.php?type=initial';
			$msp->merchant['cancel_url']       = BASE_URL . '../index.php';
			// optional automatic redirect back to the shop:
			$msp->merchant['redirect_url']     = BASE_URL . '../bedankt.php';

			
			$description = "Nog in te vullen";

			$msp->customer['locale']           = 'nl';
			$msp->customer['firstname']        = $klantnaam;
			$msp->customer['address1']         = $klantadres;
			$msp->customer['housenumber']      = $klanthuisnummer;
			$msp->customer['zipcode']          = $klantpostcode;
			$msp->customer['city']             = $klantplaats;
			$msp->customer['country']          = 'NL';
			$msp->customer['email']            = $klantemail;

			$msp->transaction['id']            =  $transactiecode;  // rand(100000000,999999999);         // $rijtrans['transactie'];  ; // generally the shop's order ID is used here
			$msp->transaction['currency']      = 'EUR';
			$msp->transaction['amount']        =  $amount;     // $amount; // cents
			$msp->transaction['description']   = "Nog in te vullen";
			// $msp->transaction['items']         = '<br/><ul><li>1 x Item1</li><li>2 x Item2</li></ul>';

			// returns a payment url
			$url = $msp->startTransaction();

			if ($msp->error){
			  echo "Error " . $msp->error_code . ": " . $msp->error . ": " . $amount . ": " . $transactiecode;
			  exit();
			}

			// redirect
			header('Location: ' . $url);

            
            } else if ($_POST['afrekenen'] == "rekening") {
                


                
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="nl">
<meta name="generator" content="PhpED 8.0">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="creation-date" content="01/01/2014">
<meta name="revisit-after" content="7 days">
<title>Bestelling succesvol geplaatst | Kroon Leveranciers BV</title>

<link rel="stylesheet" type="text/css" href="../css/style.css">
<!-- Load Fonts -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=PT+Sans:regular,bold" type="text/css" />
    <!-- Load jQuery library -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <!-- Load custom js -->
    <script type="text/javascript" src="/scripts/custom.js"></script>
<script type="text/javascript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
</head>
<body>

<div id="nav">
<div align="center" width="999">
<table width="999" cellspacing="0" cellpadding="0" border="0">
<tr>

<td class="title" align="left" width="600px"><?php contact($winkelnummer); ?></td>
<td class="inlog" width="100%" align="right" valign="middle"><?php inlog(); ?></td>
</tr>
</table>
<table width="1004" cellspacing="0" cellpadding="0" border="0">
<tr align="left">
        <td height="80">
        <div class="header_logo">
        <a href="/index.php"><img src="/afb/logo.jpg" border="0"></a>
        </div>
        </td>
        <td>
        <div class="header_zoekbalk">
                        <form action="kantoorartikelen-zoekresultaten.php" method="get">
        <table cellspacing="0" cellpadding="0" border="0"> 
        <tr valign="bottom"><td rowspan="2"><input type="text" name="zoek" id="search" autocomplete="off"></td><td rowspan="2">&nbsp;<input type="image" name="Zoeken" src="/afb/loep.jpg" value="Zoeken"/></td><td rowspan="2"><img src="/afb/sepbasket.jpg"></td></tr>
        <?php
            winkelmandje();
        ?>
        </table>
        <?php
                            zoekopmerking()            
                        ?>
                        </form>
        </div>            
        </td>
</tr>
</table>



<div class="hoofdmenu">
<table width="1004" cellspacing="0" cellpadding="0" border="0">

<tr align="left" valign="center"><td><a href="/index.php"><img src="/afb/home.png" border="0"></a></td><td class="hoofdmenu">

        <?php
            hoofdmenu($winkelnummer)            
        ?>
</td>
</tr>
</table>
</div>
</div>
</div>






    
  
<div id="wrapper">
    <div style="height:150px;">Bestelling succesvol geplaatst</div>

    
    <div id="main">
    
        <div id="bovenbalk">
            <div id="bovenbalk_left">
<table width="600" cellspacing="0" cellpadding="0" border="0">
<tr><td class="title_breadcrumb">Bestelling succesvol geplaatst</td></tr>
<tr><td class="tekst_breadcrumb"><a href="/index.php">Home</a> > Orderbevestiging</td></tr>
</table>
            </div> <!-- einde bovenbalk_left -->
            <div id="bovenbalk_right_hoog">
            </div> <!-- einde bovenbalk_right_hoog -->
            <div id="bovenbalk_right_onder">
                <?php
// Hieronder het menu met mijn pagina en dergelijke
bovenbalk($klant_id)
?>
            </div> <!-- einde bovenbalk_right_onder -->
<table width="1004" cellspacing="0" cellpadding="0" border="0">
<tr><td colspan="3"><img src="../afb/dotline.jpg" alt=""></td></tr>
</table>        

        </div> <!-- einde bovenbalk -->
                        
        <div id="main_main">

        </div>

        <div class="col_3">
        <?php
            include ("menu/menu_left.php");
        ?>
        </div> <!-- einde class col_3 -->

        <div class="col_groep">

        <div>
        <table width="778" cellspacing="0" cellpadding="0" border="0">
        <tr><td class="menu_left_title" colspan="7">Orderbevestiging</td></tr>
        <tr><td colspan="7">Wij danken u voor uw bestelling en hebben ter bevestiging een kopie van de door u geplaatste order gemaild naar het door u opgegeven mailadres <font color="e41c39"><?php echo $klantemail ?></font>.<br /><br />
        Daarnaast is uw bestelling zichtbaar via '<a href="mijnpagina.php"><b><u>Mijn pagina</u></b></a>', waar u tevens uw voorgaande bestellingen, facturen en instellingen terug kunt vinden. Houdt u er rekening mee dat deze onderdelen alleen zichtbaar zijn als u ingelogd bent.
        </td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td colspan="7"><img src="/afb/sep_mp.jpg" /></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td class="menu_left_title" colspan="7"><font color="56bcec">Facturatie en verzending</font></td></tr>
        <tr><td><hr/></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr valign="top"><td>
        <table width="778" cellspacing="0" cellpadding="0" border="0">
        <tr valign="top">
        <td width="350">
        <table width="350" cellspacing="0" cellpadding="0" border="0">
        <tr><td><font size="3"><b>Factuuradres</b></font></td></tr>
        <tr><td height="6"></td></tr>
        <tr><td><?php echo $klantnaam ?></td></tr>
        <tr><td>T.a.v.: Crediteurenadministratie</td></tr>
        <tr><td><?php echo $klantadres . " " . $klanthuisnummer ?></td></tr>
        <tr><td><?php echo $klantpostcode . " " . $klantplaats ?></td></tr>
        </table>
        </td>
        <td width="28"></td>
        <td width="350">
        <table width="350" cellspacing="0" cellpadding="0" border="0">
        <tr><td><font size="3"><b>Verzendadres</b></font></td></tr>
        <tr><td height="6"></td></tr>
        <tr><td><?php echo $verzadresrij['delnaam'] ?></td></tr>
        <tr><td>T.a.v.: <?php echo $verzadresrij['tav'] ?></td></tr>
        <tr><td><?php echo $verzadresrij['deladres1'] . " " . $verzadresrij['huisnummer'] ?></td></tr>
        <tr><td><?php echo $verzadresrij['delpostcode'] . " " . $verzadresrij['delplaats'] ?></td></tr>
        </table>
        </td>
        </tr>
        </table>
        </td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td class="menu_left_title" colspan="7"><font color="56bcec">Ordergegevens</font></td></tr>
        <tr><td><hr/></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr valign="top"><td>
        <table width="778" cellspacing="0" cellpadding="0" border="0">
        <tr valign="top">
        <td width="350">
        <table width="350" cellspacing="0" cellpadding="0" border="0">
        <tr><td colspan="2"><font size="3"><b>Klantgegevens</b></font></td></tr>
        <tr><td height="6"  colspan="2"></td></tr>
        <tr><td width="180">Uw klantnummer</td><td width="170" align="left">: <?php echo $ordergegevensrij['klant_code'] ?></td></tr>
        <tr><td width="180">Ons ordernummer</td><td width="170" align="left">: <?php echo $ordergegevensrij['id'] ?></td></tr>
        <tr><td width="180">Uw ordernummer</td><td width="170" align="left">: <?php echo $refklant ?></td></tr>
        </table>
        </td>
        <td width="28"></td>
        <td width="350">
        <table width="350" cellspacing="0" cellpadding="0" border="0">
        <tr><td colspan="2"><font size="3"><b>Facturatie instellingen</b></font></td></tr>
        <tr><td height="6"  colspan="2"></td></tr>
        <?php
        $betaaltermijn = "SELECT * FROM `klant_betaaltermijn` WHERE `id` = '$klantgegevensrij[betaaltermijn]'";
        $betaaltermijnres = mysql_query($betaaltermijn);
        while ($betaaltermijnrij = mysql_fetch_array($betaaltermijnres)) {
        if ($betaaltermijnrij['betaaltermijn'] > 0){
           $betopm = "Uw krediettermijn"; 
           $termijn = $betaaltermijnrij['betaaltermijn'] . " dagen netto";
           $mailtermijn = "Uw krediettermijn: " . $betaaltermijnrij['betaaltermijn'] . " dagen netto";
        } else {
           $betopm = "Uw krediettermijn"; 
           $termijn = "U dient vooruit te betalen";
	   $mailtermijn = "Betaling: Via iDeal";
        }
        }
        if ($facturatieper > 0){
            $factper = "E-mail";
        } else {
            $factper = "Post";
        }
        if ($klantenkorting > 0){
            $klkrting = "<tr><td width='175'>Gehanteerde klantenkorting</td><td width='175' align='left'>: " . $klantenkorting . "%</td></tr>";
	    $klmailkorting = "Gehanteerde klantenkorting " . $klantenkorting . "%";
        } else {
            $klkrting = "";
            $klmailkorting = "";
        }
        ?>
        <tr><td width="175"><?php echo $betopm ?></td><td width="175" align="left">: <?php echo $termijn ?></td></tr>
        <tr><td width="175">Facturatie per</td><td width="175" align="left">: <?php echo $factper ?></td></tr>
        <?php
            echo $klkrting;
        ?>
        </table>
        </td>
        </tr>
        </table>
        </td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td class="menu_left_title" colspan="7"><font color="56bcec">Uw bestelling</font></td></tr>
        <tr><td><hr/></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td>
        <table width="778" cellspacing="0" cellpadding="0" border="0">
        <tr>
        <td width="60"><b>Aantal</b></td>
        <td width="488"><b>Omschrijving</b></td>
        <td width="10">&nbsp;</td>
        <td width="55"><b>Art.nr.</b></td>
        <td width="70" align="right"><b>Prijs p/st</b></td>
        <td width="95" align="right"><b>Totaalbedrag</b></td>
        </tr>
        <tr><td colspan="7" height="6"></td></tr>
        <?php
        $totaalbestelling = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]'";
        $totaalbestellingres = mysql_query($totaalbestelling);
        while ($totaalbestellingrij = mysql_fetch_array($totaalbestellingres)) {
        $totaalbestellingtot = $totaalbestellingrij['SUM(prijs)']; 
        }
        $totaalhoog = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `vat` = '3'";
        $totaalhoogres = mysql_query($totaalhoog);
        while ($totaalhoogrij = mysql_fetch_array($totaalhoogres)) {
        $totaalhoogbtw = $totaalhoogrij['SUM(prijs)']; 
        }
        $totaallaag = "SELECT prijs, SUM(prijs) FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `vat` = '2'";
        $totaallaagres = mysql_query($totaallaag);
        while ($totaallaagrij = mysql_fetch_array($totaallaagres)) {
        $totaallaagbtw = $totaallaagrij['SUM(prijs)']; 
        }
        $btwdef = "SELECT * FROM `btw`";
        $btwdefres = mysql_query($btwdef);
        while ($btwdefrij = mysql_fetch_array($btwdefres)) {
        if ($btwdefrij['id'] == 2){
        $btwlaagtotaal = ($totaallaagbtw * ((100 + $btwdefrij['percentage'])/100)) - $totaallaagbtw;
        }
        if ($btwdefrij['id'] == 3){
        $btwhoogtotaal = (($totaalhoogbtw + $ordergegevensrij['verzendkosten']) * ((100 + $btwdefrij['percentage'])/100)) - $totaalhoogbtw - $ordergegevensrij['verzendkosten'];    
        }
        }
        $orderregels = "SELECT * FROM `orderline` WHERE `klant_code` = '$klant_id' AND `order_id` = '$ordergegevensrij[id]' AND `winkelnummer` = '$winkelnummer'";
        $orderregelsres = mysql_query($orderregels);
        while ($orderregelsrij = mysql_fetch_array($orderregelsres)) {
        
        $artgeg = "SELECT * FROM `artikel_overzicht` WHERE `id` = '$orderregelsrij[itemcode2]'";
        $artgegres = mysql_query($artgeg);
        while ($artgegrij = mysql_fetch_array($artgegres)) {
        $omschr = $artgegrij['titelsite'];
            }
        
        
            
            ?>
            <tr>
        <td><?php echo $orderregelsrij['number'] ?></td>
        <td><?php echo strtoupper($omschr) ?></td>
        <td>&nbsp;</td>
        <td><?php echo $orderregelsrij['itemcode2'] ?></td>
        <td align="right">&euro; <?php echo number_format(($orderregelsrij['prijs']/$orderregelsrij['number']), 2, ',', '.') ?></td>
        <td align="right">&euro; <?php echo number_format($orderregelsrij['prijs'], 2, ',', '.') ?></td>
        </tr>
        <tr><td colspan="7"><img src="/afb/dotline_artdet.jpg" /></td></tr>    
            <?php 
        }  
        ?>
        <tr><td colspan="7" height="16"></td></tr>
        <tr><td colspan="5" align="right">Totaalbedrag exclusief BTW :</td><td align="right">&euro; <?php echo number_format($totaalbestellingtot, 2, ',', '.') ?></td></tr>
        <tr><td colspan="5" align="right">Verzendkosten exclusief BTW :</td><td align="right">&euro; <?php echo number_format($ordergegevensrij['verzendkosten'], 2, ',', '.') ?></td></tr>
        <tr><td colspan="5" align="right">BTW hoog tarief over &euro; <?php echo number_format(($totaalhoogbtw + $ordergegevensrij['verzendkosten']), 2, ',', '.') ?>  :</td><td align="right">&euro; <?php echo number_format($btwhoogtotaal, 2, ',', '.') ?></td></tr>
        <tr><td colspan="5" align="right">BTW laag tarief over &euro; <?php echo number_format(($totaallaagbtw), 2, ',', '.') ?>  :</td><td align="right">&euro; <?php echo number_format($btwlaagtotaal, 2, ',', '.') ?></td></tr>
        <tr><td colspan="7" height="16"></td></tr>
        <tr><td colspan="5" align="right"><font size="3" color="e41c39"><b>Totaalbedrag inclusief BTW :</b></font></td><td align="right"><font size="3" color="e41c39"><b>&euro; <?php echo number_format($ordergegevensrij['amount'], 2, ',', '.') ?></b></font></td></tr>
        </table>
        </td></tr>
        
        
        
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td class="menu_left_title" colspan="7"><font color="56bcec">Uw mededeling aan ons</font></td></tr>
        <tr><td><hr/></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <?php
            if ($medklant != ""){
                $mededel = "<font size='3' color='000080'>" . $medklant . "</font>";
            } else {
                $mededel = "<font size='3'>U heeft geen mededeling meegestuurd met deze order</font>";
            }
        ?>
        <tr><td colspan="7"><?php echo $mededel ?></td></tr>
        <tr><td colspan="7" height="16"></td></tr>
        <tr><td colspan="7"><img src="/afb/sep_mp.jpg" /></td></tr>
        <tr><td colspan="7" height="6"></td></tr>
        <tr><td class="menu_left_title" colspan="7"><font color="000080">Overige informatie</font></td></tr>
        <tr><td colspan="7">Indien u uw bestelling heeft geplaatst op werkdagen tot 16:30 uur, vrijdagen en feestdagen v&oacute;&oacute;r 14:30 uur, dan kunt u uw bestelling de volgende werkdag verwachten per koeriersdienst.
        <br /><br />Eventuele naleveringen vinden kosteloos plaats zodra het betreffende artikel weer uit voorraad geleverd kan worden. U kunt uw naleveringen vinden via '<a href="mijnpagina.php"><b><u>Mijn pagina</u></b></a>' of vraag onze klantenservice naar de verwachte levertijd.
        <br /><br /><font color ="000080" size="4"><b>Schade en manco's</b></font>
        <br />Zodra u de bestelling ontvangt is het zaak de inhoud van de doos of dozen te inspecteren op schade of ontbrekende artikelen. Mocht u onvolkomenheden aantreffen in een levering, dan verzoeken wij u contact op te nemen met onze klantenservice via telefoonnummer 010 - 437 99 85 zodat wij dit per omgaande kunnen afhandelen voor u.
        <br /><br /><font color ="000080" size="4"><b>Retouren</b></font>
        <br />Indien u, na levering, &eacute;&eacute;n of meerdere artikelen wilt retourneren dan zijn wij genoodzaakt de aan ons in rekening gebrachte retourkosten door te berekenen aan u. Uiteraard geldt dit niet als wij u de verkeerde artikelen hebben bezorgd.
        <br /><br />De kosten die aan ons worden doorberekend vanuit onze leverancier(s) bedragen &euro; 8,95 voor het inschakelen van een koerier en per artikelregel worden er &euro; 9,95 aan ons doorberekend voor het terugplaatsen in het magazijn.
        
        </td></tr>
        </table>
        
        
        
        </div>
        </div> <!-- einde class col_groep -->

        <?php
        
        
            // =================================================================================================================================
            $transactiecode = str_pad($klant_id, 5, "0", STR_PAD_LEFT).str_pad($orderid, 5, "0", STR_PAD_LEFT);
            // =================================================================================================================================

	$datasave = "SELECT * FROM `kroon_order` WHERE `id` = '$orderid' AND `winkelnummer` = '$winkelnummer'";
     	$datasaveres = mysql_query($datasave);
     	while ($datasaverij = mysql_fetch_array($datasaveres)) {
	
	$saveverzend = "SELECT * FROM `klant_verzendadres` WHERE `id` = '$datasaverij[verzendid]'";
     	$saveverzendres = mysql_query($saveverzend);
     	while ($saveverzendrij = mysql_fetch_array($saveverzendres)) {
	$delnaamMB = $saveverzendrij['delnaam'];
	$tavMB = $saveverzendrij['tav'];
	$deladres1MB = $saveverzendrij['deladres1'];
	$huisnummerMB = $saveverzendrij['huisnummer'];
	$delpostcodeMB = $saveverzendrij['delpostcode'];
	$delplaatsMB = $saveverzendrij['delplaats'];
	$delcountryMB = $saveverzendrij['delcountry'];

	}
	}


            // ***** Verwerken van de order in kroon_order *****
            
		$order = "UPDATE `kroon_order` SET
		`transactie` = '$transactiecode',
		`status` = 'rekening',
		`delnaam` = '$delnaamMB',
		`deladres1` = '$deladres1MB',
		`tav` = '$tavMB',
		`huisnummer` = '$huisnummerMB',
		`delpostcode` = '$delpostcodeMB',
		`delplaats` = '$delplaatsMB',
		`delcountry` = '$delcountryMB',
		`referentie` = '$_POST[referentie]',
		`tekstregel` = '$_POST[mededeling]'
		WHERE `id` = '$orderid'";
            $orderres = mysql_query($order);

	

    //change this to your email.
    $to = $emailklant;
    $from = $webpassrij['emailalgemeen'];
    $subject = "Kroon Leveranciers BV : Orderbevestiging " . $orderid;

    // opbouw gegevens uit database 
    // Orderregels eerst
	$orderlineMB = "SELECT * FROM `orderline` WHERE `order_id` = '$orderid' AND `winkelnummer` = '$winkelnummer'";
 	$orderlineMBres = mysql_query($orderlineMB);
	while ($orderlineMBrij = mysql_fetch_array($orderlineMBres)) {
    
	$orderregels = '
	<tr><td colspan="8"><img src="http://'.$webpassrij['websitenaam'].'/afb/nieuwsbrief/ordlinesep.jpg"></td></tr>
	<tr>
	<td>&nbsp;</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">'. $orderlineMBrij['number'] .'</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">'. strtoupper($orderlineMBrij['omschrijving']) .'</td>
	<td>&nbsp;</td>
	<td><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt"><a href="http://' .$webpassrij['websitenaam']. '/kantoorartikelen-zoekresultaten.php?zoek='.$orderlineMBrij['itemcode2'].'">'. $orderlineMBrij['itemcode2'] .'</a></td>
	<td align="right"><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">&euro; '. number_format(($orderlineMBrij['prijs']/$orderlineMBrij['number']), 2, ',', '') .'</td>
	<td align="right"><span style="font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt">&euro; '. number_format($orderlineMBrij['prijs'], 2, ',', '') .'</td>
	<td>&nbsp;</td>
	</tr>';
	
	$orderregels2 = $orderregels2.$orderregels;

}

  
    //begin of HTML message
    $message = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
</head>
<body leftmargin='0' marginwidth='0' topmargin='0' marginheight='0' offset='0' style='-webkit-text-size-adjust: none;background-color: #C3C3C3;margin: 0px 0px 0px 0px;padding: 0px 0px 0px 0px;width: 100%;'>
<table id='backgroundTable' height='100%'' width='100%' border='0' cellpadding='0' cellspacing='0' style='height: 100%;width: 100%;margin: 0px 0px 0px 0px;padding: 0px 0px 0px 0px;''>
<tr>
<td align='center' valign='top'>
<table width='644' border='0' cellpadding='0' cellspacing='0'>
<tr><td height='10'>&nbsp;</td></tr>
<tr><td><a href='http://" . $webpassrij['websitenaam'] . "'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/headerorderbevestiging.jpg' border='0' alt='Header nieuwsbrief'></a></td></tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
U heeft zojuist een bestelling geplaatst bij " . $webpassrij['configuratienaam'] . ". Hieronder treft u de gegevens aan zoals u deze aan ons heeft doorgegeven.<br><br>
<td width='10'>&nbsp;</td>
</td></tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='300'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Factuuradres</b><br><br>
" . $klantnaam . "<br>
T.a.v.: Crediteurenadministratie<br>
" . $klantadres . " " . $klanthuisnummer . "<br>
" . $klantpostcode . " " . $klantplaats . "<br>
</span></td>
<td width='24'>&nbsp;</td>
<td width='300'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Verzendadres</b><br><br>
" . $verzadresrij['delnaam'] . "<br>
T.a.v.: " . $verzadresrij['tav'] . "<br>
" . $verzadresrij['deladres1'] . " " . $verzadresrij['huisnummer'] . "<br>
" . $verzadresrij['delpostcode'] . " " . $verzadresrij['delplaats'] . "<br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr></table>                                                                
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='300' valign='top'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Klantgegevens</b><br><br>
Uw klantnummer: " . $ordergegevensrij['klant_code'] . "<br>
Ons ordernummer: " . $ordergegevensrij['id'] . "<br>
Uw ordernummer: " . $refklant . "<br>
</span></td>
<td width='24'>&nbsp;</td>
<td width='300' valign='top'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Factuurgegevens</b><br><br>
". $mailtermijn . "<br>
Facturatie per: " . $factper . "<br>
" . $klmailkorting . "<br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr></table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<b>Uw ordergegevens</b><br><br>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='60'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Aantal</b></span></td>
<td width='314'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Omschrijving</b></span></td>
<td width='20'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
&nbsp;</span></td>
<td width='60'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Art.nr.</b></span></td>
<td width='85' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Prijs p/st</b></span></td>
<td width='85' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif; font-size:10pt'>
<b>Totaal</b></span></td>
<td width='10'>&nbsp;</td>
</tr>" . $orderregels2 ."
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr><td colspan='4' height='20'></td>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>Totaalbedrag exclusief BTW :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($totaalbestellingtot, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>Verzendkosten exclusief BTW :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($ordergegevensrij['verzendkosten'], 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>BTW hoog tarief over &euro; " . number_format(($totaalhoogbtw + $ordergegevensrij['verzendkosten']), 2, ',', '.') . " :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($btwhoogtotaal, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>BTW laag tarief over &euro; " . number_format(($totaallaagbtw), 2, ',', '.') . " :</span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>&euro; " . number_format($btwlaagtotaal, 2, ',', '.') . "</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr><td colspan='4' height='20'></td>
<tr>
<td width='10'>&nbsp;</td>
<td width='524' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'><b>Totaalbedrag inclusief BTW :</b></span></td><td width='100' align='right'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'><b>&euro; " . number_format($ordergegevensrij['amount'], 2, ',', '.') . "</b></span></td>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<br><b>Uw mededeling aan ons</b>
</span></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'></td>
<td width='10'>&nbsp;</td>
</tr>
<tr>
<td width='10'>&nbsp;</td>
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>" .$mededel  . "</span></td>
<td width='10'>&nbsp;</td>
</tr>

</table>

<table width='644' border='0' cellpadding='0' cellspacing='0' style='background-color: #FFFFFF;'>
<tr>
<td width='10'>&nbsp;</td>                                                                  
<td width='624'><span style='font-family:arial,helvetica neue,helvetica,sans-serif;'>
<br><br>
<b>Overige informatie</b><br>
<img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/ordline.jpg'><br>
Voor onze Algemene Voorwaarden, leveringsvoorwaarden en dergelijke verwijzen wij naar onze website. Zodra u ingelogd bent, kunt u tevens o.a. uw orderhistorie, facturen en spaarpunten inzien via het onderdeel 'Mijn pagina'.<br><br>Heeft u vragen en/of opmerkingen betreffende deze orderbevestiging, dan horen wij deze graag van u.

<br><br>
Met vriendelijke groet,<br><br><br>
" . $webpassrij['configuratienaam'] . "<br>
" . $webpassrij['straat'] . "  " . $webpassrij['huisnummer'] . " <br>
" . $webpassrij['postcode'] . " " . $webpassrij['plaats'] . " <br>
T. : " . $webpassrij['telefoon'] . "<br>
E. : " . $webpassrij['emailalgemeen'] . "
</span>
<td width='10'>&nbsp;</td>
</tr>
</table>
<table width='644' border='0' cellpadding='0' cellspacing='0'>
<tr><td><img src='http://".$webpassrij['websitenaam']."/afb/nieuwsbrief/bottomorderbevestiging.jpg' border='0' alt='End of nieuwsbrief'></td></tr>
</table>
</td>
</tr>
</table>
</body>
</html>
";
   //end of message
    $headers  = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    //options to send to cc+bcc
    //$headers .= "Cc: rchrd52@gmail.com";
    $headers .= "Bcc: info@kroonleveranciers.nl";
    
    // now lets send the email.
    mail($to, $subject, $message, $headers);

    //echo "<br><br>Message has been sent now....!!!!";

            
            
            
        ?>
        
        
        
    </div> <!-- einde main -->
    <div id="footer_wrapper">&nbsp;
        <div>
        </div>
    </div>
  
 </div> <!-- einde wrapper -->
<?php
menuonder()
?>

</body>
</html>

                
                
                
                
                
<?php
            }                    
?>                


