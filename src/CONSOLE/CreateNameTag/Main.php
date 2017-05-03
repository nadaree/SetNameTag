<?php
namespace CONSOLE\CreateNameTag;
use pocketmine\command\{Command,CommandSender};
use pocketmine\{Server,Player};
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener{
/////////////////
/*>>[Enabled]<<*/
/////////////////
public function onEnable(){
$t = $this;
$t->getServer()->getPluginManager()->registerEvents($t, $t);

if(!file_exists($t->getDataFolder())){mkdir($t->getDataFolder(),0774,true);}
$t->saveDefaultConfig();

$this->CheckMoneyplugins();

$t->config = new Config($t->getDataFolder(). "config.yml", Config::YAML);
$t->user = new Config($t->getDataFolder()."users.yml",Config::YAML);
$bt1 = $this->config->get("Bantitle1");
$bt2 = $this->config->get("Bantitle2");
$bt3 = $this->config->get("Bantitle3");
$this->getServer()->getLogger()->info("§4禁止ワード: $bt1 / $bt2 / $bt3");
//$this->getServer()->getLogger()->info("Bandegree: $bt1 / $bt2 / $bt3");
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
$cost = $this->config->get("金額");$name = $sender->getName();
$ww = $this->config->get("left");$w = $this->config->get("right");
if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
	$this->EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$money = EconomyAPI::getInstance()->myMoney($sender->getName());
}else{if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
	$this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
$money = PocketMoney::getInstance()->getMoney($sender->getName());
}
}
//////////////////
/*>>[Messages]<<*/
//////////////////
$m1 = "§c*§e/degree §b<§e称号名§b>";
$m2 = "§a称号を作るには$".$cost."を使います";
$m3 = "§bお金が足りません。";
$m4 = "§cその称号名は利用できません。";
$m5 = "§c本当に称号を消去しますか?(消す場合は§e/undegree <yes>§c)";
$m6 = "§a称号を消去し、代わりに$1000000を手に入れました。";
//English:
/*
$m1 = "§a>§edegree <degreename>";
$m2 = "§b>§eCreatedegree usemoney is $".$cost;
$m3 = "Your Your short of money.";
$m4 = "your write degree is Not available";
*/

if($sender instanceof Player){
switch($cmd->getName()){

	case "degree":
	$string = implode(" ", $args);
	if (empty($args[0])){
		$sender->sendMessage($m1);
		$sender->sendMessage($m2);
	    //$sender->sendMessage("注意: 一度買ったらお金は戻ってきません。");
		return;
	}
	if (stripos($args[0], "§k") === false) {}else{
		$sender->sendMessage($m4);
		break;
	}
	//trueで取得するとOPなどしかブロックできないのでOPを含むワードを禁止にできるよう、敢えてelse
	$bt1 = $this->config->get("Bantitle1");
	$bt2 = $this->config->get("Bantitle2");
	$bt3 = $this->config->get("Bantitle3");
	if (stripos($args[0], $bt1) === false) {}else{
		$sender->sendMessage($m4);
		break;
	}
	if (stripos($args[0], $bt2) === false) {}else{
		$sender->sendMessage($m4);
	}
	if (stripos($args[0], $bt3) === false) {}else{
		$sender->sendMessage($m4);
	}
	$degree = $args[0];

	if(!isset($args[1])){
		if($money < $cost){
			$sender->sendMessage($m3); break;
		}
		if($money > $cost){
				$sender->sendMessage("購入しました! 称号名:{$ww}".$degree."{$w}");
				$this->onDelMoeny($sender, $cost);
				$this->user->set($name, $degree);
				$this->user->save();
				return true;
		}
	}

	case "undegree":
	if (empty($args[0])){
		$sender->sendMessage($m5);
		return;
	}

	if(!isset($args[1])){
		if ($args[0] === "yes"){
			$sender->sendMessage($m6);
			$this->user->remove($name);
			$this->user->save();
			$coste = 1000000;
			$this->onAddMoney($sender, $coste);
		}
	}
}}
return false;

}
public function Cht(\pocketmine\event\player\PlayerChatEvent $ec)
{
$player = $ec->getPlayer();
$name = $ec->getPlayer()->getName();
$ms = $ec->getMessage();
$prefix = $this->user->get($name);
if($this->user->exists($name)){
$ec->setMessage("[".$prefix."§r] ".$ms);
}else{}}


public function onMyMoney($sender){
	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
		EconomyAPI::getInstance()->myMoney($sender->getName());
	}elseif ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		PocketMoney::getInstance()->getMoney($sender->getName());
	}
}
public function onAddMoney($sender, $coste){
	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
		EconomyAPI::getInstance()->addMoney($sender->getName(), +$coste);
	}elseif ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		PocketMoney::getInstance()->grantMoney($sender->getName() +$coste);
	}
}
public function onDelMoeny($sender, $cost){
	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
		EconomyAPI::getInstance()->reduceMoney($sender->getName(), $cost);
	}elseif ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null) {
		PocketMoney::getInstance()->grantMoney($sender->getName() -$cost);
	}
}
public function CheckMoneyplugins()
{
	$logger = $this->getServer()->getLogger();
	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null && $this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		$logger->error("PocketMoneyとEconomyAPIの両方が検出されました。EconomyAPIが優先的に使用されます。");
	}elseif($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
		$logger->info("§bEconomyAPI§rの検出をしました。");
	}elseif($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		$logger->info("§bPocketMoney§rの検出をしました。");
	}else{
		$logger->error("EconomyAPI/PocketMoneyを検出できませんでした。プラグインを終了します。");
		$this->getServer()->getPluginManager()->disablePlugin($this);
	}
}


}
