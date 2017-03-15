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

if($t->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
$t->EconomyAPI = $t->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$t->getLogger()->info("§bEconomyAPIの検出を完了");
//$t->getLogger()->info("§bEconomyAPI is found!");
}else{
	if ($t->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		$this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
		$this->getLogger()->info("PocketMoneyを検出しました。");
		//$t->getLogger()->info("§bPocketMOney is found!");
	}else{
$t->getLogger()->critical("EconomyAPI/PocketMoneyが見つかりません。終了します");
//$t->getLogger()->critical("EconomyAPI/pocketmoney is not found. plugin disable:(");
$t->getServer()->getPluginManager()->disablePlugin($this);}}

$t->config = new Config($t->getDataFolder(). "config.yml", Config::YAML);
$t->user = new Config($t->getDataFolder()."users.yml",Config::YAML);
$bt1 = $this->config->get("Bantitle1");
$bt2 = $this->config->get("Bantitle2");
$bt3 = $this->config->get("Bantitle3");
$this->getServer()->getLogger()->info("§4禁止ワード: $bt1 / $bt2 / $bt3");
//$this->getServer()->getLogger()->info("Bantitle: $bt1 / $bt2 / $bt3");
}
public function onCommand(CommandSender $use, Command $cmd, $label, array $args){
$cost = $this->config->get("金額");$name = $use->getName();
$ww = $this->config->get("left");$w = $this->config->get("right");
if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
	$this->EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$money = EconomyAPI::getInstance()->myMoney($use->getName());
}else{if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
	$this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
$money = PocketMoney::getInstance()->getMoney($use->getName());
}
}
//////////////////
/*>>[Messages]<<*/
//////////////////
$m1 = "*/title <称号名>";
$m2 = "称号を作るには$".$cost."を使います";
$m3 = "お金が足りません。";
$m4 = "その称号名は利用できません。";
//English:
/*
$m1 = "§a>§etitle <titlename>";
$m2 = "§b>§eCreateTitle usemoney is $".$cost;
$m3 = "Your Your short of money.";
$m4 = "your write title is Not available";
*/

if($use instanceof Player){
switch($cmd->getName()){

	case "title":
	$string = implode(" ", $args);
	if (empty($args[0])){
		$use->sendMessage($m1);
		$use->sendMessage($m2);
	    //$use->sendMessage("注意: 一度買ったらお金は戻ってきません。");
		return;
	}
	if (stripos($args[0], "§k") === false) {}else{
		$use->sendMessage($m4);
		break;
	}
	//trueで取得するとOPなどしかブロックできないのでOPを含むワードを禁止にできるよう、敢えてelse
	$bt1 = $this->config->get("Bantitle1");
	$bt2 = $this->config->get("Bantitle2");
	$bt3 = $this->config->get("Bantitle3");
	if (stripos($args[0], $bt1) === false) {}else{
		$use->sendMessage($m4);
		break;
	}
	if (stripos($args[0], $bt2) === false) {}else{
		$use->sendMessage($m4);
	}
	if (stripos($args[0], $bt3) === false) {}else{
		$use->sendMessage($m4);
	}
	$title = $args[0];

/*<<-------------------------------------[reduceMoney]---------------------------------------------->>*/
	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
		$this->EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->EconomyAPI->reduceMoney($use->getName(), $cost);
	}else{
	if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney") != null){
		$this->PocketMoney = $this->getServer()->getPluginManager()->getPlugin("PocketMoney");
		$plpl = PocketMoney::getInstance()->getMoney($use->getName());
		$prize = $plpl - $cost;
		$buyem = PocketMoney::getInstance()->getMoney($use->getName() - $cost);
	}}
/*<<------------------------------------------------------------------------------------------------>>*/
	if(!isset($args[1])){
		if($money < $cost){
			$use->sendMessage($m3); break;
		}
		if($money > $cost){
				$use->sendMessage("購入しました! 称号名:{$ww}".$title."{$w}");
				$buyem;
				$this->user->set($name, $title);
				$this->user->save();
				return true;
		}
}}}
return false;

}
public function Cht(\pocketmine\event\player\PlayerChatEvent $ec)
{
$player = $ec->getPlayer();
$name = $ec->getPlayer()->getName();
$ms = $ec->getMessage();
$prefix = $this->user->get($name);
if($this->user->exists($name)){
$ec->setMessage("[".$prefix."§r]".$ms);
}else{}}}
