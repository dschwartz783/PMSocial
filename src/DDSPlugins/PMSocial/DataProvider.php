<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/28/16
 * Time: 1:42 AM
 */

namespace DDSPlugins\PMSocial;


use pocketmine\Player;

abstract class DataProvider
{
    protected $plugin;
    protected $json_path;
    protected $list;

    function __construct(PMSocial $plugin, String $jsonName)
    {
        $this->plugin = $plugin;
        $this->json_path = $plugin->getDataFolder() . $jsonName;

        if (!is_dir($plugin->getDataFolder())) {
            mkdir($plugin->getDataFolder());
        }

        $file = null;
        if (!is_file($this->json_path)) {
            $file = fopen($this->json_path, "w+");
            fwrite($file, "{}");
            $this->list = [];
        } else {
            $file = fopen($this->json_path, "r");
            $this->list = json_decode(fread($file, filesize($this->json_path)), true);
        }
        fclose($file);

    }

    function addPlayer(Player $sourcePlayer, Player $addedPlayer) {
        if (!key_exists(strtolower($addedPlayer->getName()), $this->list)) {
            $this->list[strtolower($addedPlayer->getName())] = [strtolower($sourcePlayer->getName())];
        } else {
            $this->list[strtolower($addedPlayer->getName())] += [strtolower($sourcePlayer->getName())];
        }
        $this->update_json();
    }

    function removePlayer(Player $sourcePlayer, String $addedPlayer, &$addedPlayerFull = null) {
        foreach (array_keys($this->list) as $key) {
            if (preg_match(";^$addedPlayer;i", $key)) {
                $addedPlayer = $key;
                $temp_array = [];
                foreach ($this->list[strtolower($addedPlayer)] as $player) {
                    if ($player != strtolower($sourcePlayer->getName())) {
                        $temp_array += [$player];
                    }
                }
                if (count($temp_array) === count($this->list[strtolower($addedPlayer)])) {
                    continue;
                } else {
                    $addedPlayerFull = $addedPlayer;
                }
                //remove array to replace with temp one
                unset($this->list[strtolower($addedPlayer)]);
                $this->list[strtolower($addedPlayer)] = $temp_array;
                $this->update_json();
                return true;
            }
        }
        return false;
    }

    function update_json() {
        $file = fopen($this->json_path, "w");
        fwrite($file, json_encode($this->list));
        fclose($file);
    }

    function getListForPlayer(Player $player) {

        $player_list = [];
        foreach ($this->list as $added_player => $added_player_list) {
            foreach ($added_player_list as $player_who_added) {

                if (strtolower($player->getName()) == strtolower($player_who_added)) {
                    array_push($player_list, $added_player);
                }
            }
        }
        return $player_list;
    }

    function checkAdded(Player $sourcePlayer, Player $destinationPlayer) {
        if (array_key_exists(strtolower($sourcePlayer->getName()), $this->list)) {
            foreach ($this->list[strtolower($sourcePlayer->getName())] as $added_by_player) {
                if ($added_by_player === strtolower($destinationPlayer->getName())) {
                    return true;
                }
            }
        }
        return false;
    }
}