<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 2:25 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class IgnoreListener implements Listener
{
    /** @var PMSocial $plugin */
    private $plugin;

    /** @var IgnoreListDataProvider $ignoreListDataProvider */
    public $ignoreListDataProvider;

    function __construct(PMSocial $plugin)
    {
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        $this->ignoreListDataProvider = new IgnoreListDataProvider($plugin);
        $this->plugin = $plugin;
    }

    function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        $args = explode(" ", $event->getMessage());
        if (count($args) == 2) {
            $tpa_player = $this->plugin->getServer()->getPlayer($args[1]);
            if ($tpa_player != null && ($args[0] == "/tpa" || $args[0] == "/tpahere") && $this->ignoreListDataProvider->checkAdded($event->getPlayer(), $tpa_player)) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage("This player is ignoring you. Teleport blocked.");
            }
        }
    }

    function onPlayerChat(PlayerChatEvent $event){
        $allowedRecipients = [];
        if ($event->getPlayer() instanceof Player) {
            foreach ($event->getRecipients() as $recipient) {
                //if recipient is instance of Player
                if ($recipient instanceof Player) {
                    //if that player is not ignored
                    if (!$this->ignoreListDataProvider->checkAdded($event->getPlayer(), $recipient)) {
                        array_push($allowedRecipients, $recipient);
                    }
                } else {
                    //if it isn't a player (console
                    array_push($allowedRecipients, $recipient);
                }
            }
        }

        $event->setRecipients($allowedRecipients);
    }
}