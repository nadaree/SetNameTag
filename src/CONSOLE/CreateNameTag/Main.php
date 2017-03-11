<?php
namespace CONSOLE\CreateNameTag; 
use pocketmine\command\{Command,CommandSender};
use pocketmine\{Server,Player};
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener{
public function onEnable(){
$t = $this;
$t->saveDefaultConfig();
$t->getServer()->getPluginManager()->registerEvents($t, $t);

if(!file_exists($t->getDataFolder())){mkdir($t->getDataFolder(),0774,true);}

if($t->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
$t->EconomyAPI = $t->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$t->getLogger()->info("§bEconomyAPIの検出を完了");
}else{
$t->getLogger()->critical("EconomyAPIが見つかりません。終了します");
$t->getServer()->getPluginManager()->disablePlugin($this);}

$t->config = new Config($t->getDataFolder(). "config.yml", Config::YAML);
$t->BT = new Config($t->getDataFolder(). "BanTitle.yml", Config::YAML);
$t->titles = new Config($t->getDataFolder(). "user.yml", Config::YAML);

}
public function onCommand(CommandSender $use, Command $cmd, $label, array $args){
$cost = $this->config->get("金額");$name = $use->getName();
$words = $this->BT->get("words");
$ww = $this->config->get("left");$w = $this->config->get("right");
$money = EconomyAPI::getInstance()->myMoney($use->getName());
//メッセージ
$m1 = "*/title <称号名>";
$m2 = "称号を作るには$".$cost."を使います";
$m3 = "お金が足りません。";

if($use instanceof Player){
switch($cmd->getName()){

	case "title":
	if (empty($args[0])){
		$use->sendMessage($m1);
		$use->sendMessage($m2);
	    //$use->sendMessage("注意: 一度買ったらお金は戻ってきません。");
		return;
	}

	$title = $args[0];

	if(!isset($args[1])){
		$nan = isset($args[1]);
		if($money < $cost){
			$use->sendMessage($m3); break;
			}
		if($money > $cost){
			if($title == $words || $title == "§k"){
				$use->sendMessage("その称号名は使用できません");
				return false;
			}
				$use->sendMessage("購入しました! 称号名:{$ww}".$title."{$w}");
				EconomyAPI::getInstance()->reduceMoney($name, $cost);
				$this->titles->set($name, $title);
				$this->titles->save();
				return true;
		}
}}}
return false;
}

public function Chat(\pocketmine\event\player\PlayerChatEvent $ec)
{
$player = $ec->getPlayer();
$ww = $this->config->get("left");$w = $this->config->get("right");
$name = $ec->getPlayer()->getName();
$ms = $ec->getMessage();
$prefix = $this->titles->get($name);
if($this->titles->exists($name)){
$ec->setMessage("{$ww}".$prefix."{$w}".$ms);
}else{}}}