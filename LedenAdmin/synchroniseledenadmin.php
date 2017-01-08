<?php

class synchroniseledenadmin {
	private $feusers;
	private $cmsmailer;
	private $lid;
	
	public function __construct($feusers, $cmsmail) 
	{
		$this->feusers = $feusers;
		$this->cmsmailer = $cmsmail;
	}
	
	public function syncAdres($feu_id, $lid_ebk) {
		$userProp = $this->feusers->GetUserProperties($feu_id);
		$verschillen = array();
		
		if ($this->getUserProperty($userProp, "address1") != $lid_ebk['adres']) {
			$feuData = $this->getUserProperty($userProp, "address1");
			$this->feusers->setUserPropertyFull("address1",$lid_ebk['adres'],$feu_id); 
			$verschillen[] = "Verschil in Adres, wordt aangepast van $feuData naar ".$lid_ebk['adres'];
		}

		if ($this->getUserProperty($userProp, "address2") != "") {
			$feuData = $this->getUserProperty($userProp, "address2");
			$this->feusers->setUserPropertyFull("address2","",$feu_id);
			$verschillen[] = "Adresregel 2 komt te vervallen";
		}

		if ($this->getUserProperty($userProp, "zipcode") != $lid_ebk['postcode']) {
			$feuData = $this->getUserProperty($userProp, "zipcode");
			$this->feusers->setUserPropertyFull("zipcode",$lid_ebk['postcode'],$feu_id);
			$verschillen[] = "Verschil in Postcode, wordt aangepast van $feuData naar ".$lid_ebk['postcode'];
		}
	
		if ($this->getUserProperty($userProp, "residence") != $lid_ebk['woonplaats']) {
			$feuData = $this->getUserProperty($userProp, "residence");
			$this->feusers->setUserPropertyFull("residence",$lid_ebk['woonplaats'],$feu_id);
			$verschillen[] = "Verschil in Woonplaats, wordt aangepast van $feuData naar ".$lid_ebk['woonplaats'];
		}

		if ($this->getUserProperty($userProp, "telhome") != $lid_ebk['telefoon']) {
			$feuData = $this->getUserProperty($userProp, "telhome");
			$this->feusers->setUserPropertyFull("telhome",$lid_ebk['telefoon'],$feu_id);
			$verschillen[] = "Verschil in Telefoonnr, wordt aangepast van $feuData naar ".$lid_ebk['telefoon'];
		}

		if ($this->getUserProperty($userProp, "telmobile") != $lid_ebk['mobiel']) {
			$feuData = $this->getUserProperty($userProp, "telmobile");
			$this->feusers->setUserPropertyFull("telmobile",$lid_ebk['mobiel'],$feu_id);
			$verschillen[] = "Verschil in Mobielnr, wordt aangepast van $feuData naar ".$lid_ebk['mobiel'];
		}

		if ($this->getUserProperty($userProp, "email") != $lid_ebk['email']) {
			$feuData = $this->getUserProperty($userProp, "email");
			$feuEmail = $feuData;
			$this->feusers->setUserPropertyFull("email",$lid_ebk['email'],$feu_id);
			$verschillen[] = "Verschil in email, wordt aangepast van $feuData naar ".$lid_ebk['email'];
		}

		if (!empty($verschillen)) {
		    echo "verschillen gevonden <br />";
			$mailContent = "Beste " . $lid_ebk['naam']. ", \n\n";

			$mailContent .= "Je gegevens op de ledensite zijn aangepast n.a.v. wijzigingen in de administratie, "; 
	        $mailContent .= "het betreft de volgende verschillen: \n\n";
			foreach ($verschillen as $verschil) {
				$mailContent .= $verschil . "\n";
			}
			$mailContent .= "\nJe kunt je gegevens controleren op de ledenpagina van zcflevo.nl. ";
			$mailContent .= "Mochten er zaken niet kloppen geef dit dan door aan de secretaris.";
			$mailContent .= "\n\nMet vriendelijke groet,\n\nwebmaster\n";
			
			$this->cmsmailer->reset();
        
			if (filter_var($lid_ebk['email'], FILTER_VALIDATE_EMAIL)) {
				$this->cmsmailer->AddAddress($lid_ebk['email'], $lid_ebk['naam']);
			} else {
				$this->cmsmailer->AddAddress('webmaster@zcflevo.nl');
			}
		
			if (isset($feuEmail)) {
				if (filter_var($feuEmail, FILTER_VALIDATE_EMAIL)) {
					$this->cmsmailer->AddAddress($feuEmail);
				}
			} 
			$this->cmsmailer->AddCC('secretaris@zcflevo.nl', 'secretaris ZCFlevo');		
			$this->cmsmailer->AddBCC('webmaster@zcflevo.nl','webmaster');
	        
			$this->cmsmailer->AddReplyTo('secretaris@zcflevo.nl', 'secretaris ZCFlevo');
			$this->cmsmailer->SetSubject('Wijzigingen in administratie');
			$this->cmsmailer->SetBody($mailContent);
			$this->cmsmailer->IsHTML(false);
			$this->cmsmailer->Send(); 
	    }
	}

	private function getUserProperty($userProp, $title) {
		foreach( $userProp as $oneprop )
		{
			if( $oneprop['title'] == $title )
			{
				return $oneprop['data'];
			}
		}
		return "";
	}
}
