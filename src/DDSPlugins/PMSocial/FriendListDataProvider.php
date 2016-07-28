<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/19/16
 * Time: 1:10 AM
 */

namespace DDSPlugins\PMSocial;

class FriendListDataProvider extends DataProvider
{
    /** @var PMSocial $plugin */
    protected $plugin;

    /** @var string $friend_json_path*/
    protected $friend_json_path = "hi";

    /** @var array $friend_list */
    protected $friend_list;

    function __construct(PMSocial $plugin) {
        parent::__construct($plugin, "friends.json");
        $this->friend_list = $this->list;
        $this->friend_json_path = $this->json_path;
    }
}