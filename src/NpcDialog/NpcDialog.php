<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);


namespace NpcDialog;


use pocketmine\plugin\Plugin;

class NpcDialog {

    /** @var bool */
    static private $registered = false;

    static public function register(Plugin $plugin): void {
        if(!self::$registered) {
            $plugin->getServer()->getPluginManager()->registerEvents(new PacketListener(), $plugin);
            self::$registered = true;
        }
    }

}