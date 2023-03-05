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
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\NpcDialoguePacket;
use pocketmine\network\mcpe\protocol\NpcRequestPacket;
use pocketmine\Server;

class PacketListener implements Listener{

	/** @var array<string, int> */
	private array $responsePool = [];

	public function onPacketReceiveEvent(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if($packet instanceof NpcRequestPacket){
			$this->handleNpcRequest($event);
		}
	}

	private function handleNpcRequest(DataPacketReceiveEvent $event){
		/** @var NpcRequestPacket $packet */
		if(($packet = $event->getPacket()) instanceof NpcRequestPacket){
			$server = Server::getInstance();
			$player = $event->getOrigin()->getPlayer();
			$entity = $server->getWorldManager()->findEntity($packet->actorRuntimeId);
			if($entity === null) return;

			$username = $player->getName();
			$logger = $server->getLogger();

			$logger->debug("Received NpcRequestPacket from $username");

			switch($packet->requestType){
				case NpcRequestPacket::REQUEST_EXECUTE_ACTION:
					$logger->debug("Received a NpcRequestPacket action " . $packet->actionIndex);
					$this->responsePool[$username] = $packet->actionIndex;
					break;
				case NpcRequestPacket::REQUEST_EXECUTE_CLOSING_COMMANDS:
					$form = DialogFormStore::getFormByEntity($entity);
					if($form !== null){
						$form->handleResponse($player, $this->responsePool[$username] ?? null);
						unset($this->responsePool[$username]);
					}else{
						$logger->warning("Unhandled NpcRequestPacket for $username because there wasn't a registered form on the store");
					}
					break;
				default:
				{
					$logger->warning("Unhandled NpcRequestPacket for $username because the request type was unknown");
					$logger->debug("NpcRequestPacket request type: " . $packet->requestType . " action index: " . $packet->actionIndex . " command: " . $packet->commandString . " runtime id: " . $packet->actorRuntimeId . " scene name: " . $packet->sceneName);
				}
			}
			$event->cancel();
		}
	}

	public function onPlayerEntityInteractEvent(PlayerEntityInteractEvent $event) : void{
		$player = $event->getPlayer();
		$entity = $event->getEntity();
		$server = Server::getInstance();
		$logger = $server->getLogger();
		$username = $player->getName();
		$logger->debug("Received PlayerEntityInteractEvent from $username");
		$pk = NpcDialoguePacket::create($entity->getId(), NpcDialoguePacket::ACTION_OPEN, "hello world", "", $entity->getNameTag(), "");
		$player->getNetworkSession()->sendDataPacket($pk);
	}

}