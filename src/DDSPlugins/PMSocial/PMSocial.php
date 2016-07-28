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

class PMSocial extends PluginBase
{
    /** @var IgnoreListener $ignoreListener */
    private $ignoreListener;

    /** @var IgnoreListDataProvider $ignoreListDataProvider */
    private $ignoreListDataProvider;

    /** @var FriendListDataProvider $friendListDataProvider */
    private $friendListDataProvider;

    function onEnable()
    {
        $this->ignoreListener = new IgnoreListener($this);
        $this->ignoreListDataProvider = $this->ignoreListener->ignoreListDataProvider;
        $this->friendListDataProvider = new FriendListDataProvider($this);
        $this->getLogger()->info("PMSocial Enabled");
    }

    function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case "ignorelist":
                    $ignore_list_string = "§cPlayers you're ignoring:\n";
                    $player_ignore_list = $this->ignoreListDataProvider->getListForPlayer($sender);
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
                case "friendlist":
                    $friend_list_string = "§cPlayers you're friends with:\n";
                    $player_friend_list = $this->friendListDataProvider->getListForPlayer($sender);
                    if ($player_friend_list != []) {
                        foreach ($player_friend_list as $player) {
                            $friend_list_string .= ("§a - " . $player . "\n");
                        }
                    } else {
                        $friend_list_string .= "§a - None";
                    }
                    $sender->sendMessage($friend_list_string);
                    return true;
            }
            if (count($args) == 1) {
                //These cases cannot use the $argPlayer variable, because argPlayer may be null, if the player isn't online
                $temp_player = null;
                switch($command->getName()) {
                    case "unignore":
                        if (!$this->ignoreListDataProvider->removePlayer($sender, $args[0], $temp_player)) {
                            $sender->sendMessage("You aren't ignoring " . $args[0] . "!");
                        } else {
                            $sender->sendMessage("Unignoring player: " . $temp_player);
                        }
                        return true;
                        break;
                    case "unfriend":
                        if (!$this->friendListDataProvider->removePlayer($sender, $args[0], $temp_player)) {
                            $sender->sendMessage("You aren't friends with " . $args[0] . "!");
                        } else {
                            $sender->sendMessage("Unfriending player: " . $temp_player);
                        }
                        return true;
                        break;
                }
                $argPlayer = $this->getServer()->getPlayer($args[0]);
                if ($argPlayer != null) {
                    switch ($command->getName()) {
                        case "ignore":
                            if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                $sender->sendMessage("Unfriending player: " . $argPlayer->getName());
                                $this->friendListDataProvider->removePlayer($sender, $argPlayer->getName());
                                $sender->sendMessage("Ignoring player: " . $argPlayer->getName());
                                $this->ignoreListDataProvider->addPlayer($sender, $argPlayer);
                            } else {
                                $sender->sendMessage("You can't ignore yourself!");
                            }
                            return true;
                            break;

                        case "friend":
                            if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                $sender->sendMessage("Unignoring player: " . $argPlayer->getName());
                                $this->ignoreListDataProvider->removePlayer($sender, $argPlayer->getName());
                                $sender->sendMessage("Friending player: " . $argPlayer->getName());
                                $this->friendListDataProvider->addPlayer($sender, $argPlayer);
                            } else {
                                $sender->sendMessage("You can't friend yourself!");
                            }
                            return true;
                            break;
                        case "tpfriend":
                            if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                if ($this->friendListDataProvider->checkAdded($sender, $argPlayer)) {
                                    $sender->sendMessage("Teleporting you to: " . $argPlayer->getName());
                                    $sender->teleport($argPlayer);
                                } else {
                                    $sender->sendMessage("That player has not friended you!");
                                }
                            } else {
                                $sender->sendMessage("You can't teleport to yourself!");
                            }
                            return true;
                            break;
                    }
                }  else {
                    $sender->sendMessage("Player cannot be found");
                }
            }
        } else {
            $this->getLogger()->error("Command cannot be run from console");
            return true;
        }
        return false;
    }
}
