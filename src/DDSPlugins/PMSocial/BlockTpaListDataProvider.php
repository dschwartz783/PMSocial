<?php
/**
 * Created by PhpStorm.
 * User: funtimes
 * Date: 7/28/16
 * Time: 3:23 AM
 */

namespace DDSPlugins\PMSocial;

class BlockTpaListDataProvider extends DataProvider
{
    function __construct(PMSocial $plugin) {
        parent::__construct($plugin, "tpablocks.json");
    }
}