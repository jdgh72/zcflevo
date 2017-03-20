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
	
	public function syncStatus($feu_id, $lid_ebk) {
	    $statusVerslag = array();
        $ebk_status=strtoupper($lid_ebk['status']);
	    if ($lid_ebk['lid_tot'] == "") {
	    // Actief lid
	       $ebk_status=strtoupper($lid_ebk['status']);
	       if ($ebk_status == "VLIEG") {
	          $statusVerslag = $this->checkVliegendLid($feu_id, $lid_ebk['naam']);
	       } elseif ($ebk_status == "ADMIN") {
	          $statusVerslag = $this->checkAdministratiefLid($feu_id, $lid_ebk['naam']);
	       } elseif ($ebk_status == "DONATEUR") {
	          // Donateur kan lid zijn geweest, maar dat hoeft niet.
	          $statusVerslag = $this->checkDonateurschap($feu_id, $lid_ebk['naam']);
            } elseif ($ebk_status == "ERELID") {
                $statusVerslag = $this->checkErelid($feu_id, $lid_ebk['naam']);
            } else {
              $statusVerslag = $this->unknownStatus($feu_id, $ebk_status, $lid_ebk['naam']);
	       }
	    } else {
	       if ($ebk_status == "") {
	          $statusVerslag = $this->checkGeenLid($feu_id, $lid_ebk['naam']);
	       } elseif ($ebk_status == "DONATEUR") {
	          $statusVerslag = $this->checkDonateurschap($feu_id, $lid_ebk['naam']);
	       } else {
	          $statusVerslag = $this->invalidStatus($feu_id, $ebk_status, $lid_ebk['naam']);
	       }
	    }
	    
	    if (!empty($statusVerslag)) {
			$mailContent = "Beste secretaris, \n\n";

			$mailContent .= "Bij synchronisatie van de ledenadministratie tussen site en e-boekhouden "; 
	        $mailContent .= "is het volgende geconstateerd: \n\n";
			foreach ($statusVerslag as $verslagRegel) {
				$mailContent .= $verslagRegel . "\n";
			}
			$mailContent .= "\n";
			$mailContent .= "Met vriendelijke groet,\n\nwebmaster\n";
			
			$this->cmsmailer->reset();
        
			$this->cmsmailer->AddAddress('webmaster@zcflevo.nl','webmaster');		
			//$this->cmsmailer->AddCC('webmaster@zcflevo.nl','webmaster');
	        
			$this->cmsmailer->AddReplyTo('webmaster@zcflevo.nl','webmaster');
			$this->cmsmailer->SetSubject('Synchronisatie status lid');
			$this->cmsmailer->SetBody($mailContent);
			$this->cmsmailer->IsHTML(false);
			$this->cmsmailer->Send(); 
        }
	}

	
	private function checkVliegendLid($feu_id, $naam) {
	     return $this->checkStatus($feu_id, 13, $naam);
	}
	
	private function checkAdministratiefLid($feu_id, $naam) {
	     return $this->checkStatus($feu_id, 15, $naam);
	}
	
	private function checkDonateurschap($feu_id, $naam) {
	     return $this->checkStatus($feu_id, 16, $naam);
	}
	
    private function checkErelid($feu_id, $naam) {
        return $this->checkStatus($feu_id, 18, $naam);
    }
    
	private function checkGeenLid($feu_id, $naam) {
	     return $this->checkStatus($feu_id, 0, $naam);
	}
	
	private function unknownStatus($feu_id, $ebk_status, $naam) {
        $status = array();
        $status[] = "Onjuiste status in E-boekhouden voor ".$naam;
        $status[] = "Verwacht een lidstatus VLIEG, ADMIN of DONATEUR omdat veld lid_tot niet is ingevuld";
        $status[] = "Gevonden status in e-boekhouden is: ".$ebk_status;
        return $status; 
	}
	
	private function invalidStatus($feu_id, $ebk_status, $naam) {
        $status = array();
        $status[] = "Onjuiste status in E-boekhouden voor ".$naam;
        $status[] = "Veld lid_tot is ingevuld wat erop duidt dat lidmaatschap geeindigd is";
        $status[] = "Verwacht status niet ingevuld of status DONATEUR, status is echter: ".$ebk_status;
	    return $status;
	}
	
	
	private function checkStatus($feu_id, $group_id, $naam) {
	    $wijzigingsVerslag = array();
	    // 1: Leden
	    if (!$this->feusers->MemberOfGroup($feu_id, 1)) {
	        $this->feusers->AssignUserToGroup($feu_id, 1);
	        $wijzigingsVerslag[] = "Lid ".$naam. " toegevoegd aan groep Leden.";
	    }
	    
	    // 13: vliegend
	    if ($this->feusers->MemberOfGroup($feu_id, 13)) {
	        if ($group_id != 13) {
	           $this->feusers->RemoveUserFromGroup($feu_id, 13);
	           $wijzigingsVerslag[] = "Lid ".$naam. " verwijderd van groep vliegend.";
	        }
	    } else {
	        if ($group_id == 13) {
   	            $this->feusers->AssignUserToGroup($feu_id, 13);
	            $wijzigingsVerslag[] = "Lid ".$naam. " toegevoegd aan groep vliegend.";
	        }
	    }
	    
	    // 15: administratief
	    if ($this->feusers->MemberOfGroup($feu_id, 15)) {
	        if ($group_id != 15) {
	           $this->feusers->RemoveUserFromGroup($feu_id, 15);
	           $wijzigingsVerslag[] = "Lid ".$naam. " verwijderd van groep administratief.";
	        }
	    } else {
	        if ($group_id == 15) {
   	            $this->feusers->AssignUserToGroup($feu_id, 15);
	            $wijzigingsVerslag[] = "Lid ".$naam. " toegevoegd aan groep administratief.";
	        }
	    }

	    // 16: donateur
	    if ($this->feusers->MemberOfGroup($feu_id, 16)) {
	        if ($group_id != 16) {
	           $this->feusers->RemoveUserFromGroup($feu_id, 16);
	           $wijzigingsVerslag[] = "Lid ".$naam. " verwijderd van groep donateur.";
	        }
	    } else {
	        if ($group_id == 16) {
   	            $this->feusers->AssignUserToGroup($feu_id, 16);
	            $wijzigingsVerslag[] = "Lid ".$naam. " toegevoegd aan groep donateur.";
	        }
	    }
        
        //18: Erelid
        if ($this->feusers->MemberOfGroup($feu_id, 18)) {
	        if ($group_id != 18) {
	           $this->feusers->RemoveUserFromGroup($feu_id, 18);
	           $wijzigingsVerslag[] = "Lid ".$naam. " verwijderd van groep ereleden.";
	        }
	    } else {
	        if ($group_id == 18) {
   	            $this->feusers->AssignUserToGroup($feu_id, 18);
	            $wijzigingsVerslag[] = "Lid ".$naam. " toegevoegd aan groep ereleden.";
	        }
	    }
	    
	    return $wijzigingsVerslag;
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
?>
