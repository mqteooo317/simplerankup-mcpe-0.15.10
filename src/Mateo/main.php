<?php

namespace Mateo;

use pocketmine\plugin\PLuginBase as PB;
use pocketmine\event\Listener as L;
use pocketmine\{Player, Server, utils\Config, event\player\PlayerJoinEvent, event\player\PLayerQuitEvent, command\Command, command\CommandSender};

class main extends PB implements L{

	private $v = "0.0.1";
	private $ranks;
	private $players;

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveResource("ranks.yml", false);
		$this->ranks = new Config($this->getDataFolder()."ranks.yml", Config::YAML);
		$this->players = new Config($this->getDataFolder()."players.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("SimpleRankUp enabled!");
		$this->getServer()->broadcastMessage("Thanks for using SimpleRankup v{$this->v} made by mqteo!");
	}

	public function onJoin(PlayerJoinEvent $ev){
		$p = $ev->getPlayer();
		$n = strtolower($p->getName());
		$defRank = "usuario";
		if($this->players->getNested("players.$n.rank", null) === null){
			$allRanks = $this->ranks->get("ranks", []);
			foreach($allRanks as $rankName => $data){
				if(isset($data["default"]) AND $data["default"] === "true"){
					$defRank = $rankName;
					break;
				}
			}
		}

		$this->players->setNested("players.$n.rank", $defRank);
		$this->players->save();
		$p->sendMessage("§a[Rank] Bienvenido(a). Debido a que eres nuevo(a) se te ha asignado el rango: " . $defaultRank);
	}

	public function onCommand(CommandSender $s, Command $c, $lbl, array $args){
		$n = strtolower($s->getName());
		if(strtolower($c->getName()) === "sru"){
			if(!$s instanceof Player) return false;
			if(!isset($args[0]) OR strtolower($args[0]) !== "info"){
				$crrRank = $this->players->getNested("players.$n.rank");
				//if $crrRank es igual a null(caso raro)
				if($crrRank === null){
					$s->sendMessage("§a[Rank] No tienes rango?");
					return true;
				}

				$rankData = $this->ranks->getNested("ranks.$crrRank");
				if($rankData === null) return false;
				$nextRank = $rankData["next"];
				if($nextRank === null OR $nextRank === "null"){
					$s->sendMessage("§a[Rank] Ya has alcanzado el rango maximo.");
					return true;
				}

				$this->players->setNested("players.$n.rank", $nextRank);
				$this->players->save();
				$s->sendMessage("§a[Rank] Has subido al rango {$nextRank}.");
				return true;
			}
			if(strtolower($args[0]) === "info"){
				if(!$s instanceof Player) return false;
				$usrRank = $this->players->getNested("players.$n.rank");
				$next = $this->ranks->getNested("ranks.$usrRank.next");
				$nxtRank = ($next !== null) ? $next : "Rango maximo";
				$msg = "Informacion: ";
				$msg .= " Tu rango: {$usrRank}.";
				$msg .= " Siguiente rango: {$nxtRank}.";
				$msg .= " v{$this->v} - SimpleRankUp";
        $msg .= " made by mqteo.";
				$s->sendMessage($msg);
				return true;
			}
			return false;
		}
	}	
}
