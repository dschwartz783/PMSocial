<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 1:10 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\Player;
use pocketmine\utils\Config;
use DDSPlugins\PMSocial\PMSocial;
use pocketmine\command\CommandSender;

class IgnoreListDataProvider
{
    /** @var PMSocial $plugin */
    private $plugin;

    /** @var string $ignore_json_path*/
    private $ignore_json_path;

    /** @var array $ignore_list */
    private $ignore_list;

    function __construct(PMSocial $plugin)
    {
        $this->plugin = $plugin;
        $this->ignore_json_path = $plugin->getDataFolder() . "ignores.json";

        if (!is_dir($plugin->getDataFolder())) {
            mkdir($plugin->getDataFolder());
        }

        $ignore_file = null;
        if (!is_file($this->ignore_json_path)) {
            $ignore_file = fopen($this->ignore_json_path, "w+");
            fwrite($ignore_file, "{}");
            $this->ignore_list = [];
        } else {
            $ignore_file = fopen($this->ignore_json_path, "r");
            $this->ignore_list = json_decode(fread($ignore_file, filesize($this->ignore_json_path)), true);
        }
        fclose($ignore_file);

    }

    function getIgnoreList() {
        return $this->ignore_list;
    }

    function ignorePlayer(Player $sourcePlayer, Player $ignoredPlayer) {
        if (!key_exists(strtolower($ignoredPlayer->getName()), $this->ignore_list)) {
            $this->ignore_list[strtolower($ignoredPlayer->getName())] = [strtolower($sourcePlayer->getName())];
        } else {
            $this->ignore_list[strtolower($ignoredPlayer->getName())] += [strtolower($sourcePlayer->getName())];
        }
        $this->update_json();
    }

    function unignorePlayer(Player $sourcePlayer, string $ignoredPlayer) {
        if (isset($this->ignore_list[strtolower($ignoredPlayer)])) {
            $temp_array = [];
            foreach ($this->ignore_list[strtolower($ignoredPlayer)] as $player) {
                if ($player != strtolower($sourcePlayer->getName())) {
                    $temp_array += [$player];
                }
            }
            unset($this->ignore_list[strtolower($ignoredPlayer)]);
            $this->ignore_list[strtolower($ignoredPlayer)] = $temp_array;
            $this->update_json();
            return true;
        }
        return false;
    }

    function getIgnoreListForPlayer(Player $player) {
        if (array_key_exists(strtolower($player->getName()), $this->ignore_list)) {
            return $this->ignore_list[strtolower($player->getName())];
        } else {
            return [];
        }
    }

    function checkIgnore(Player $sourcePlayer, Player $destinationPlayer) {
        if (array_key_exists(strtolower($sourcePlayer->getName()), $this->ignore_list)) {
            foreach ($this->ignore_list[strtolower($sourcePlayer->getName())] as $ignored_by_player) {
                if ($ignored_by_player === strtolower($destinationPlayer->getName())) {
                    return true;
                }
            }
        }
        return false;
    }

    function update_json() {
        $ignore_file = fopen($this->ignore_json_path, "w");
        fwrite($ignore_file, json_encode($this->ignore_list));
        fclose($ignore_file);
    }

    function cleanUp() {
        $this->plugin->getLogger()->info("cleaning up...");
    }
}