<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);


namespace NpcDialog;


use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\NpcRequestPacket;

class PacketListener implements Listener {

    /** @var mixed[] */
    private $responsePool = [];

    public function onPacketReceiveEvent(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        $server = $player->getServer();

        if(!($packet instanceof NpcRequestPacket) or ($entity = $server->findEntity($packet->entityRuntimeId)) === null) {
            return;
        }

        $username = $player->getName();
        $logger = $server->getLogger();

        switch($packet->requestType) {
            case NpcRequestPacket::REQUEST_EXECUTE_ACTION:
                $logger->debug("Received a NpcRequestPacket action" . $packet->actionType);
                $this->responsePool[$username] = $packet->actionType;
                break;
            case NpcRequestPacket::REQUEST_EXECUTE_CLOSING_COMMANDS:
                $form = DialogFormStore::getFormByEntity($entity);
                if($form !== null) {
                    $form->handleResponse($player, $this->responsePool[$username] ?? null);
                    unset($this->responsePool[$username]);
                } else {
                    $logger->warning("Unhandled NpcRequestPacket for $username because there wasn't a registered form on the store");
                }
                break;
        }

    }

}