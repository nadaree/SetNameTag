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
$t->user = new Config($t->getDataFolder()."user.yml",Config::YAML);
$t->config = new Config($t->getDataFolder() . "config.yml", Config::YAML, array('金額' => '1000000'));

if($t->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
$t->EconomyAPI = $t->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$t->getLogger()->info("§bEconomyAPIの検出を完了");
//$t->getLogger()->info("§bEconomyAPI is found!");
}else{
$t->getLogger()->critical("EconomyAPIが見つかりません。終了します");
//$t->getLogger()->critical("EconomyAPI is not found. plugin disable:(");
$t->getServer()->getPluginManager()->disablePlugin($this);}
}
public function onCommand(CommandSender $use, Command $cmd, $label, array $args){
$cost = $this->config->get("金額");$name = $use->getName();
$money = EconomyAPI::getInstance()->myMoney($use->getName());
//////////////////
/*>>[Messages]<<*/
//////////////////
$m1 = "*/title <称号名>";
$m2 = "称号を作るには$".$cost."を使います";
$m3 = "お金が足りません。";
//English:
/*
$m1 = "§a>§etitle <titlename>";
$m2 = "§b>§eCreateTitle usemoney is $".$cost;
$m3 = "Your Your short of money.";
*/

if($use instanceof Player){
switch($cmd->getName()){

	case "title":
	if (empty($args[0])){
		$use->sendMessage($m1);
		$use->sendMessage($m2);
		return;
	}

	$title = $args[0];

	if(!isset($args[1])){
		$nan = isset($args[1]);
		if($money < $cost){
			$use->sendMessage($m3); break;
			}
		if($money > $cost){
			$use->sendMessage("購入しました! 称号名:[".$title."§r]");
				//$use->sendMessage("Purchase! titlename:[".$title."§r]");
				EconomyAPI::getInstance()->reduceMoney($name, $cost);
				$this->user->set($name, $title);
				$this->user->save();
				return true;
		}
}}}
return false;
}

//////////////////
///*>>[Chat]<<*///
//////////////////
public function Cht(\pocketmine\event\player\PlayerChatEvent $ec)
{
$player = $ec->getPlayer();
$name = $ec->getPlayer()->getName();
$ms = $ec->getMessage();
$prefix = $this->user->get($name);
if($this->user->exists($name)){
$ec->setMessage("[".$prefix."§r]".$ms);
}else{}}}