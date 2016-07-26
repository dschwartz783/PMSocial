<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 1:10 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\Player;
use DDSPlugins\PMSocial\PMSocial;
use pocketmine\command\CommandSender;

class FriendListDataProvider
{
    /** @var PMSocial $plugin */
    private $plugin;

    /** @var string $friend_json_path*/
    private $friend_json_path;

    /** @var array $friend_list */
    private $friend_list;

    function __construct(PMSocial $plugin)
    {
        $this->plugin = $plugin;
        $this->friend_json_path = $plugin->getDataFolder() . "friends.json";

        if (!is_dir($plugin->getDataFolder())) {
            mkdir($plugin->getDataFolder());
        }

        $friend_file = null;
        if (!is_file($this->friend_json_path)) {
            $friend_file = fopen($this->friend_json_path, "w+");
            fwrite($friend_file, "{}");
            $this->friend_list = [];
        } else {
            $friend_file = fopen($this->friend_json_path, "r");
            $this->friend_list = json_decode(fread($friend_file, filesize($this->friend_json_path)), true);
        }
        fclose($friend_file);

    }

    function getFriendList() {
        return $this->friend_list;
    }

    function friendPlayer(Player $sourcePlayer, Player $friendedPlayer) {
        if (!key_exists(strtolower($friendedPlayer->getName()), $this->friend_list)) {
            $this->friend_list[strtolower($friendedPlayer->getName())] = [strtolower($sourcePlayer->getName())];
        } else {
            $this->friend_list[strtolower($friendedPlayer->getName())] += [strtolower($sourcePlayer->getName())];
        }
        $sourcePlayer->sendMessage("Friending player: " . $friendedPlayer->getName());
        $this->update_json();
    }

    function unfriendPlayer(Player $sourcePlayer, string $friendedPlayer) {
        if (isset($this->friend_list[strtolower($friendedPlayer)])) {
            $temp_array = [];
            foreach ($this->friend_list[strtolower($friendedPlayer)] as $player) {
                if ($player != strtolower($sourcePlayer->getName())) {
                    $temp_array += [$player];
                }
            }
            unset($this->friend_list[strtolower($friendedPlayer)]);
            $this->friend_list[strtolower($friendedPlayer)] = $temp_array;
            $sourcePlayer->sendMessage("Unfriending player: " . $friendedPlayer);
            $this->update_json();
            return true;
        }
        return false;
    }

    function getFriendListForPlayer(Player $player) {

        $player_list = [];
        foreach ($this->friend_list as $friended_player => $friended_player_list) {
            foreach ($friended_player_list as $player_who_friended) {

                if (strtolower($player->getName()) == strtolower($player_who_friended)) {
                    array_push($player_list, $friended_player);
                }
            }
        }
        return $player_list;
    }

    function checkFriend(Player $sourcePlayer, Player $destinationPlayer) {
        if (array_key_exists(strtolower($sourcePlayer->getName()), $this->friend_list)) {
            foreach ($this->friend_list[strtolower($sourcePlayer->getName())] as $friended_by_player) {
                if ($friended_by_player === strtolower($destinationPlayer->getName())) {
                    return true;
                }
            }
        }
        return false;
    }

    function update_json() {
        $friend_file = fopen($this->friend_json_path, "w");
        fwrite($friend_file, json_encode($this->friend_list));
        fclose($friend_file);
    }

    function cleanUp() {
        $this->plugin->getLogger()->info("cleaning up...");
    }
}