<?php

$whitelist = json_decode(file_get_contents("whitelist.json"),1);

$stats = [ 
	"total_mints" => 0,
	"private_mints" => 0,
	"exploit_private_mints" => 0,
	"whitelist_mints" => 0,
];

$exploitingAddresses = [];

$fp = fopen("etherscan_contract_txes.txt", "r");

while($row = fgets($fp)){
	$tx = json_decode($row,1);
	
	if(!$tx['isError']){
		$privateMinted = (strpos($tx["input"],"0x32850bf6") === 0);
		$whitelistMinted = (strpos($tx["input"],"0x9f41554a") === 0);
		
		if($privateMinted || $whitelistMinted){
			$mintAmount = hexdec(trim(substr($tx["input"], 72, 2)));
			
			$stats["total_mints"] += $mintAmount;
			
			$whitelisted = isset($whitelist[$tx['from']]);
			if($whitelisted){
				if($privateMinted){
					$stats["exploit_private_mints"] += $mintAmount;
					
					if(!isset($exploitingAddresses[$tx['from']])){
						$exploitingAddresses[$tx['from']] = [
							"total_exploted_mints" => 0,
							"txes" => [],
						];
					}
					
					$exploitingAddresses[$tx['from']]["total_exploted_mints"] += $mintAmount;
					$exploitingAddresses[$tx['from']]["txes"] = [
						"url" => "https://etherscan.io/tx/".$tx['hash'],
						"amount" => $mintAmount,
					];
					
				}else{
					$stats["whitelist_mints"] += $mintAmount;
				}
			}else{
				$stats["private_mints"] += $mintAmount;
			}
		}
	}
}

print_r($stats);

file_put_contents("ExploitingAddresses.txt", print_r($exploitingAddresses,1));

?>