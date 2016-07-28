<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 1:10 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\Player;

class IgnoreListDataProvider extends DataProvider
{
    /** @var PMSocial $plugin */
    protected $plugin;

    /** @var string $ignore_json_path*/
    protected $ignore_json_path;

    /** @var array $ignore_list */
    protected $ignore_list;

    function __construct(PMSocial $plugin) {
        parent::__construct($plugin, "ignores.json");
        $this->ignore_list = $this->list;
        $this->ignore_json_path = $this->json_path;
    }

    function addPlayer(Player $sourcePlayer, Player $ignoredPlayer) {
        if (!$sourcePlayer->isOp()) {
            if (!$ignoredPlayer->isOp()) {
                parent::addPlayer($sourcePlayer, $ignoredPlayer);
            } else {
                $sourcePlayer->sendMessage("You cannot ignore staff. If you have any issues with a staff member, please contact an administrator.");
            }
        } else {
            $sourcePlayer->sendMessage("Staff are not allowed to ignore players.");
        }
    }
}