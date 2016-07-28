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

    /** @var BlockTpaListDataProvider */
    private $blockTpaListDataProvider;

    function onEnable()
    {
        $this->ignoreListener = new IgnoreListener($this);
        $this->ignoreListDataProvider = $this->ignoreListener->ignoreListDataProvider;
        $this->blockTpaListDataProvider = $this->ignoreListener->blockTpaListDataProvider;
        $this->friendListDataProvider = new FriendListDataProvider($this);
        $this->getLogger()->info("PMSocial Enabled");
    }

    function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if ($sender instanceof Player) {
            switch ($command->getName()) {
                case "ignorelist":
                    $this->sendList($this->ignoreListDataProvider, $sender, "§cPlayers you're ignoring:\n");
                    return true;
                case "friendlist":
                    $this->sendList($this->friendListDataProvider, $sender, "§cPlayers you're friends with:\n");
                    return true;
                case "blocktpalist":
                    $this->sendList($this->blockTpaListDataProvider, $sender, "§cPlayers you're tpa blocking:\n");
                    return true;
            }
            if (count($args) == 1) {
                //These cases cannot use the $argPlayer variable, because argPlayer may be null, if the player isn't online
                $temp_player = null;
                switch($command->getName()) {
                    case "unignore":
                        if ($this->ignoreListDataProvider->removePlayer($sender, $args[0], $temp_player)) {
                            if ($this->blockTpaListDataProvider->removePlayer($sender, $args[0])) {
                                $sender->sendMessage("Unblocking tpa requests from: " . $temp_player);
                            }
                            $sender->sendMessage("Unignoring player: " . $temp_player);
                        } else {
                            $sender->sendMessage("You aren't ignoring " . $args[0] . "!");
                        }
                        return true;
                    case "unfriend":
                        if ($this->friendListDataProvider->removePlayer($sender, $args[0], $temp_player)) {
                            $sender->sendMessage("Unfriending player: " . $temp_player);
                        } else {
                            $sender->sendMessage("You aren't friends with " . $args[0] . "!");
                        }
                        return true;
                }
                $argPlayer = $this->getServer()->getPlayer($args[0]);
                if ($argPlayer != null) {
                    switch ($command->getName()) {
                        case "ignore":
                            if (!$sender->isOp()) {
                                if (!$argPlayer->isOp()) {
                                    if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                        if ($this->friendListDataProvider->removePlayer($sender, $argPlayer->getName())) {
                                            $sender->sendMessage("Unfriending player: " . $argPlayer->getName());
                                        }
                                        $sender->sendMessage("Ignoring player: " . $argPlayer->getName());
                                        $this->ignoreListDataProvider->addPlayer($sender, $argPlayer);
                                        $sender->sendMessage("Blocking tpa requests from: " . $argPlayer->getName());
                                        $this->blockTpaListDataProvider->addPlayer($sender, $argPlayer);
                                    } else {
                                        $sender->sendMessage("You can't ignore yourself!");
                                    }
                                } else {
                                    $sender->sendMessage("You cannot ignore staff. If you have any issues with a staff member, please contact an administrator.");
                                }
                            } else {
                                $sender->sendMessage("Staff are not allowed to ignore players.");
                            }
                            return true;

                        case "friend":
                            if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                if ($this->ignoreListDataProvider->removePlayer($sender, $argPlayer->getName())) {
                                    $sender->sendMessage("Unignoring player: " . $argPlayer->getName());
                                }
                                if ($this->blockTpaListDataProvider->removePlayer($sender, $argPlayer->getName())) {
                                    $sender->sendMessage("Unblocking tpa requests from: " . $argPlayer->getName());
                                }
                                $sender->sendMessage("Friending player: " . $argPlayer->getName());
                                if (!$this->ignoreListDataProvider->checkAdded($sender, $argPlayer) && !$this->friendListDataProvider->checkAdded($sender, $argPlayer)) {
                                    $argPlayer->sendMessage($sender->getName() . " has added you as a friend!\nType /friend " . $sender->getName() . " to add them back!");
                                }
                                $this->friendListDataProvider->addPlayer($sender, $argPlayer);
                            } else {
                                $sender->sendMessage("You can't friend yourself!");
                            }
                            return true;

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

                        case "blocktpa":
                            if (!$argPlayer->isOp()) {
                                if (strtolower($sender->getName()) != strtolower($argPlayer->getName())) {
                                    $sender->sendMessage("Blocking tpa requests from: " . $argPlayer->getName());
                                    $this->blockTpaListDataProvider->addPlayer($sender, $argPlayer);
                                } else {
                                    $sender->sendMessage("You can't block yourself!");
                                }
                            } else {
                                $argPlayer->sendMessage("You cannot block tpa requests from staff. If you have any issues with a staff member, please contact an administrator.");
                            }
                            return true;
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

    function sendList(DataProvider $dataProvider, Player $sender, String $header) {
        $list_string = $header;
        $player_friend_list = $dataProvider->getListForPlayer($sender);
        if ($player_friend_list != []) {
            foreach ($player_friend_list as $player) {
                $list_string .= ("§a - " . $player . "\n");
            }
        } else {
            $list_string .= "§a - None";
        }
        $sender->sendMessage($list_string);
    }
}
