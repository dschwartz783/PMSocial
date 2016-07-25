<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 2:25 AM
 */

namespace DDSPlugins\PMSocial;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class ChatListener implements Listener
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

    function onPlayerChat(PlayerChatEvent $event){
        $allowedRecipients = [];
        if ($event->getPlayer() instanceof Player) {
            foreach ($event->getRecipients() as $recipient) {
                //if recipient is instance of Player
                if ($recipient instanceof Player) {
                    //if that player is not ignored
                    if (!$this->ignoreListDataProvider->checkIgnore($event->getPlayer(), $recipient)) {
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