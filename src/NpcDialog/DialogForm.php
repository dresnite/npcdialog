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
use pocketmine\entity\Entity;
use pocketmine\form\FormValidationException;
use pocketmine\Player;
use pocketmine\utils\Utils;

class DialogForm {

    /** @var string */
    private $dialogText;

    /** @var Button[] */
    private $buttons = [];

    /** @var Entity|null */
    private $entity = null;

    /** @var Closure|null */
    private $closeListener = null;

    public function __construct(string $dialogText) {
        $this->dialogText = $dialogText;
        DialogFormStore::registerForm($this);

        $this->onCreation();
    }

    public function getDialogText(): string {
        return $this->dialogText;
    }

    public function setDialogText(string $dialogText): void {
        $this->dialogText = $dialogText;

        if($this->entity !== null) {
            $this->entity->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, $this->dialogText);
        }
    }

    public function addButton(Button $button): void {
        $this->buttons[] = $button;
    }

    public function getEntity(): ?Entity {
        return $this->entity;
    }

    public function getCloseListener(): ?Closure {
        return $this->closeListener;
    }

    public function setCloseListener(?Closure $closeListener): void {
        if($closeListener !== null) {
            Utils::validateCallableSignature(function(Player $player) {}, $closeListener);
        }
        $this->closeListener = $closeListener;
    }

    public function executeCloseListener(Player $player): void {
        if($this->closeListener !== null) {
            ($this->closeListener)($player);
        }
    }

    public function pairWithEntity(Entity $entity): void {
        if($entity instanceof Player) {
            throw new InvalidArgumentException("NpcForms can't be paired with players.");
        }

        if($this->entity !== null) {
            $this->entity->getDataPropertyManager()->setByte(Entity::DATA_HAS_NPC_COMPONENT, 0);
        }

        if(($otherForm = DialogFormStore::getFormByEntity($entity)) !== null) {
            DialogFormStore::unregisterForm($otherForm);
        }

        $this->entity = $entity;

        $propertyManager = $entity->getDataPropertyManager();
        $propertyManager->setByte(Entity::DATA_HAS_NPC_COMPONENT, 1);
        $propertyManager->setString(Entity::DATA_INTERACTIVE_TAG, $this->dialogText);
        $propertyManager->setString(Entity::DATA_NPC_ACTIONS, json_encode($this->buttons));
    }

    public function handleResponse(Player $player, $response): void {
        if($response === null) {
            $this->executeCloseListener($player);
        } elseif(is_int($response) and array_key_exists($response, $this->buttons)) {
            $this->buttons[$response]->executeSubmitListener($player);
        } else {
            throw new FormValidationException("Couldn't validate DialogForm with response $response");
        }
    }

    protected function onCreation(): void {}

}