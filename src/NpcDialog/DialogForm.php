<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace NpcDialog;

use Closure;
use InvalidArgumentException;
use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;
use pocketmine\entity\Entity;
use pocketmine\form\FormValidationException;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

class DialogForm{
	use MarshalTrait;

	#[Field]
	private string $id;

	/** @var Button[] */
	private array $buttons = [];

	private ?Entity $entity = null;

	private ?Closure $closeListener = null;

	public function __construct(private string $dialogText, ?Closure $closeListener = null){
		$this->setCloseListener($closeListener);
		DialogFormStore::registerForm($this);

		$this->onCreation();
	}

	public function getId() : string{ return $this->id; }

	public function getDialogText() : string{
		return $this->dialogText;
	}

	/** @return $this */
	public function setDialogText(string $dialogText) : self{
		$this->dialogText = $dialogText;

		$this->entity?->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, $this->dialogText);
		return $this;
	}

	/** @return $this */
	public function addButton(string $name, ?Closure $submitListener = null) : self{
		$this->buttons[] = new Button($name, $submitListener);
		return $this;
	}

	public function getEntity() : ?Entity{
		return $this->entity;
	}

	public function getCloseListener() : ?Closure{
		return $this->closeListener;
	}

	/** @return $this */
	public function setCloseListener(?Closure $closeListener) : self{
		if($closeListener !== null){
			Utils::validateCallableSignature(function(Player $player){ }, $closeListener);
		}
		$this->closeListener = $closeListener;

		return $this;
	}

	public function executeCloseListener(Player $player) : void{
		if($this->closeListener !== null){
			($this->closeListener)($player);
		}
	}

	/** @return $this */
	public function pairWithEntity(Entity $entity) : self{
		if($entity instanceof Player){
			throw new InvalidArgumentException("NpcForms can't be paired with players.");
		}

		$this->entity?->getNetworkProperties()->setByte(EntityMetadataProperties::HAS_NPC_COMPONENT, 0);

		if(($otherForm = DialogFormStore::getFormByEntity($entity)) !== null){
			DialogFormStore::unregisterForm($otherForm);
		}

		$this->entity = $entity;

		$propertyManager = $entity->getNetworkProperties();
		$propertyManager->setByte(EntityMetadataProperties::HAS_NPC_COMPONENT, 1);
		$propertyManager->setString(EntityMetadataProperties::INTERACTIVE_TAG, $this->dialogText);
		$propertyManager->setString(EntityMetadataProperties::NPC_ACTIONS, json_encode($this->buttons));

		return $this;
	}

	public function handleResponse(Player $player, $response) : void{
		if($response === null){
			$this->executeCloseListener($player);
		}elseif(is_int($response) and array_key_exists($response, $this->buttons)){
			$this->buttons[$response]->executeSubmitListener($player);
		}else{
			throw new FormValidationException("Couldn't validate DialogForm with response $response");
		}
	}

	protected function onCreation() : void{ }

}