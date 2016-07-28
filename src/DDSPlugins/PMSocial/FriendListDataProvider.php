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
    function __construct(PMSocial $plugin) {
        parent::__construct($plugin, "friends.json");
    }
}