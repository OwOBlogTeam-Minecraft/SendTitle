<?php

/*                             Copyright (c) 2017-2018 TeaTech All right Reserved.
 *
 *      ████████████  ██████████           ██         ████████  ██           ██████████    ██          ██
 *           ██       ██                 ██  ██       ██        ██          ██        ██   ████        ██
 *           ██       ██                ██    ██      ██        ██          ██        ██   ██  ██      ██
 *           ██       ██████████       ██      ██     ██        ██          ██        ██   ██    ██    ██
 *           ██       ██              ████████████    ██        ██          ██        ██   ██      ██  ██
 *           ██       ██             ██          ██   ██        ██          ██        ██   ██        ████
 *           ██       ██████████    ██            ██  ████████  ██████████   ██████████    ██          ██
**/

namespace Teaclon\SendTitle;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;


class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{
	
	const NORMAL_PRE = "§f[§aSendTitle§f] ";
	
	const CONFIG_MAIN_WORLD                     = "主城";
	const CONFIG_PLAYER_JOIN_MAIN_WORLD_MESSAGE = "玩家进入主城时显示的信息";
	const CONFIG_CUSTOM_MESSAGES                = "自定义消息";
	
	private $cache = [];
	
	
	
	
	public function onEnable()
	{
		@\mkdir($this->getDataFolder(), 0777, \true);
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML,
		[
			self::CONFIG_MAIN_WORLD             => $this->getServer()->getDefaultLevel()->getName(),
			self::CONFIG_PLAYER_JOIN_MAIN_WORLD_MESSAGE => "[大]欢迎来到这个服务器[/大][小]请遵守本服务器的游戏规则.[/小]",
			self::CONFIG_CUSTOM_MESSAGES        => [],
		]);
		
		$this->getServer()->getLogger()->info(self::NORMAL_PRE."-----------------------------------------------------------");
		$this->getServer()->getLogger()->info(self::NORMAL_PRE."§2Copyright (c) 2016-".date("Y")." Teaclon/TeaTech All Reserved.");
		$this->getServer()->getLogger()->info(self::NORMAL_PRE."§bThis Plugin §edeveloped by §bTeaclon§f(§6锤子§f)");
		$this->getServer()->getLogger()->info(self::NORMAL_PRE."§eCommand: §d/§6st");
		$this->getServer()->getLogger()->info(self::NORMAL_PRE."-----------------------------------------------------------");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		switch($command->getName())
		{
			case "st":
				if(!isset($args[0]))
				{
					if($sender->isOp())
					{
						$sender->sendMessage(self::NORMAL_PRE."§a☮§6>§f------§bsendTitle §5帮助助手§f------§6<§a☮");
						$sender->sendMessage(self::NORMAL_PRE."§f用法: §d/§6st add §f<§e地图名称§f> §f<§e大标题§f> §f<§e小标题§f>   在一个指定的世界添加一个大标题");
						$sender->sendMessage(self::NORMAL_PRE."§f用法: §d/§6st del §f<§e地图名称§f>                     删除这个世界的标题数据");
						$sender->sendMessage(self::NORMAL_PRE."§f用法: §d/§6st reload                             §f重新读取配置文件 (§e你可以直接在配置文件里面更改, 更改完成之后输入 §d/§6st reload §e即可§b立刻生效§e修改完成的配置文件§f)");
					}
					return \true;
				}
				
				switch($args[0])
				{
					default:
						$this->onCommand($sender, $command, $label, []);
						return \true;
					break;
					
					
					case "add":
						if(!$sender->isOp())
						{
							$sender->sendMessage(self::NORMAL_PRE."§c你没有权限执行这个指令.");
							return \true;
						}
						if(!isset($args[1], $args[2]) && !isset($this->cache[$sender->getName()]))
						{
							$sender->sendMessage(self::NORMAL_PRE."§c请输入需要指定的地图与大标题, 还有附加选项小标题(可以不填).");
							return \true;
						}
						if((!$this->getServer()->isLevelLoaded($args[1]) || !isset($this->config->get("自定义消息")[$args[1]])) && !isset($this->cache[$sender->getName()]))
						{
							$this->cache[$sender->getName()] = $args[2];
							$sender->sendMessage(self::NORMAL_PRE."§c地图 {$args[1]} 未加载或不存在, 请换一个地图名称. 你填写的标题信息已加入缓存队列, 更换一个正确的地图名称即可添加.");
							$sender->sendMessage(self::NORMAL_PRE."§b输入指令 §d/§6st add §f<§e新的地图名称§f> §b即可.");
							return \true;
						}
						if($args[1] === $this->config->get(self::CONFIG_MAIN_WORLD))
						{
							$sender->sendMessage(self::NORMAL_PRE."§c该地图名称与主城名称冲突, 无法添加.");
							return \true;
						}
						if(isset($args[1]) && !isset($this->config->get(self::CONFIG_CUSTOM_MESSAGES)[$args[1]]) && isset($this->cache[$sender->getName()]))
						{
							$this->config->setNested(self::CONFIG_CUSTOM_MESSAGES.".".$args[1], $this->cache[$sender->getName()]);
							$this->config->save();
							$sender->sendMessage(self::NORMAL_PRE."§a已从缓存列表中添加了地图 §e{$args[1]} §a的显示标题 §e".$this->cache[$sender->getName()]." §a.");
							unset($this->cache[$sender->getName()]);
							return \true;
						}
						if(isset($this->config->get(self::CONFIG_CUSTOM_MESSAGES)[$args[1]]))
						{
							$sender->sendMessage(self::NORMAL_PRE."§c配置文件中已存在地图的配置, 请先移除当前数据配置后在进行新的数据配置.");
							return \true;
						}
						return \true;
					break;
					
					
					case "del":
						if(!$sender->isOp())
						{
							$sender->sendMessage(self::NORMAL_PRE."§c你没有权限执行这个指令.");
							return \true;
						}
						if(!isset($args[1]))
						{
							$sender->sendMessage(self::NORMAL_PRE."§c请输入需要删除的地图名称.");
							return \true;
						}
						if(!isset($this->config->get(self::CONFIG_CUSTOM_MESSAGES)[$args[1]]))
						{
							$sender->sendMessage(self::NORMAL_PRE."§c地图数据 §e{$args[1]} §c不存在.");
							return \true;
						}
						$a = $this->config->getAll();
						unset($a[self::CONFIG_CUSTOM_MESSAGES][$args[1]]);
						$this->config->setAll($a);
						$this->config->save();
						$sender->sendMessage(self::NORMAL_PRE."§a地图数据 §e{$args[1]} §a已删除.");
						return \true;
					break;
					
					
					case "reload":
						if(!$sender->isOp())
						{
							$sender->sendMessage(self::NORMAL_PRE."§c你没有权限执行这个指令.");
							return \true;
						}
						$this->config->reload();
						$sender->sendMessage(self::NORMAL_PRE."§a配置文件重载完成.");
						return \true;
					break;
				}
			break;
		}
	}
	
	
	public function onPlayerJoin(PlayerJoinEvent $e)
	{
		$player = $e->getPlayer();
		if($player->getLevel()->getName() === $this->config->get(self::CONFIG_MAIN_WORLD)) $this->sendTitleToPlayer($player, 1, $player->getLevel());
	}
	
	public function onPlayerLevelChange(EntityLevelChangeEvent $e)
	{
		$entity = $e->getEntity();
		if($entity instanceof Player) $this->sendTitleToPlayer($entity, 2, $e->getTarget());
	}
	
	
	
	
	public function sendTitleToPlayer(Player $player, int $type, \pocketmine\level\Level $level) : bool
	{
		$msg = "";
		if($type === 1) $msg = $this->config->get(self::CONFIG_PLAYER_JOIN_MAIN_WORLD_MESSAGE);
		elseif(($type === 2) && isset($this->config->get(self::CONFIG_CUSTOM_MESSAGES)[$level->getName()])) $msg = $this->config->get(self::CONFIG_CUSTOM_MESSAGES)[$level->getName()];
		elseif($level->getName() === $this->config->get(self::CONFIG_MAIN_WORLD)) $msg = $this->config->get(self::CONFIG_PLAYER_JOIN_MAIN_WORLD_MESSAGE);
		else return \false;
		if(strlen($msg) === 0)
		{
			throw new \Exception("配置文件出现问题导致无法发送大标题给玩家! 请控制台留意该信息!\n地图名称: {$level->getName()}");
			return \false;
		}
		$bool_mainTitle = (bool) preg_match_all("/\[大](.*)\[\/大\]/i", $msg, $mainTitle);
		$bool_subTitle  = (bool) preg_match_all("/\[小](.*)\[\/小\]/i", $msg, $subTitle);\
		if($bool_mainTitle && $bool_subTitle)
		{
			$mainTitle = $mainTitle[1][0];
			$subTitle  = $subTitle[1][0];
		}
		elseif($bool_mainTitle && !$bool_subTitle)
		{
			$mainTitle = $mainTitle[1][0];
			$subTitle  = "";
		}
		elseif(!$bool_mainTitle && !$bool_subTitle && is_string($msg) && (strlen($msg) > 0))
		{
			$mainTitle = $msg;
			$subTitle  = "";
		}
		else
		{
			throw new \Exception("配置文件出现问题导致无法发送大标题给玩家! 请控制台留意该信息!\n地图名称: {$level->getName()}");
			return \false;
		}\
		if($this->getServer()->getName() === "GenisysPro") sleep(2);
		$player->removeTitles();
		$player->addTitle($mainTitle, $subTitle, 20, 150, 20);
		unset($msg, $mainTitle, $subTitle, $bool_mainTitle, $bool_subTitle);
		return \true;
	}
	
	
	
	
	
	
}
?>
