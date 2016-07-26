<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 6/19/16
 * Time: 8:08 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use DDSPlugins\PMSocial\IgnoreListDataProvider;

class PMSocial extends PluginBase
{
    /** @var IgnoreListener $ignoreListener */
    private $ignoreListener;

    /** @var IgnoreListDataProvider $ignoreListDataProvider */
    private $ignoreListDataProvider;

    function onEnable()
    {
        $this->ignoreListener = new IgnoreListener($this);
        $this->ignoreListDataProvider = $this->ignoreListener->ignoreListDataProvider;
        $this->getLogger()->info("PMSocial Enabled");
    }

    function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case "ignore":
                    if (count($args) == 1) {
                        if (strtolower($sender->getName()) != strtolower($args[0])) {
                            if ($this->getServer()->getPlayerExact($args[0]) != null) {
                                $this->ignoreListDataProvider->ignorePlayer($sender, $this->getServer()->getPlayerExact($args[0]));
                            } else {
                                $sender->sendMessage("Player cannot be found");
                            }
                        } else {
                            $sender->sendMessage("You can't ignore yourself!");
                        }
                        return true;
                    }
                    break;

                case "unignore":
                    if (count($args) == 1) {
                        if (!$this->ignoreListDataProvider->unignorePlayer($sender, $args[0])) {
                            $sender->sendMessage("You aren't ignoring " . $args[0] . "!");
                        }
                        return true;
                    }
                    break;

                case "ignorelist":
                    $ignore_list_string = "§cPlayers you're ignoring:\n";
                    $player_ignore_list = $this->ignoreListDataProvider->getIgnoreListForPlayer($sender);
                    if ($player_ignore_list != []) {
                        foreach ($player_ignore_list as $player) {
                            $ignore_list_string .= ("§a - " . $player . "\n");
                        }
                    } else {
                        $ignore_list_string .= "§a - None";
                    }
                    $sender->sendMessage($ignore_list_string);
                    return true;
                    break;
                default:
                    break;
            }
        } else {
            $this->getLogger()->error("Command cannot be run from console");
            return true;
        }
        return false;
    }

    function onDisable()
    {
        $this->ignoreListDataProvider->cleanUp();
    }
}
