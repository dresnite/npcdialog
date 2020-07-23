<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);


namespace NpcDialog;


use InvalidArgumentException;
use JsonSerializable;
use pocketmine\entity\Entity;
use pocketmine\Player;

class DialogForm implements JsonSerializable {

    /** @var string */
    private $dialog;

    /** @var Button[] */
    private $buttons = [];

    /** @var Entity|null */
    private $entity = null;

    public function __construct(string $dialog) {
        $this->dialog = $dialog;
        DialogFormStore::registerForm($this);
    }

    public function getDialog(): string {
        return $this->dialog;
    }

    public function setDialog(string $dialog): void {
        $this->dialog = $dialog;
    }

    public function addButton(Button $button): void {
        $this->buttons[] = $button;
    }

    public function hasEntity(): bool {
        return $this->entity !== null;
    }

    public function getEntity(): ?Entity {
        return $this->entity;
    }

    public function pairWithEntity(Entity $entity): void {
        if($entity instanceof Player) {
            throw new InvalidArgumentException("NpcForms can't be paired with players.");
        }

        if($this->entity !== null) {
            $this->entity->getDataPropertyManager()->setByte(Entity::DATA_HAS_NPC_COMPONENT, 0);
        }

        $propertyManager = $entity->getDataPropertyManager();
        $propertyManager->setByte(Entity::DATA_HAS_NPC_COMPONENT, 1);
        $propertyManager->setString(Entity::DATA_INTERACTIVE_TAG, "Testing wth is this??");
        $propertyManager->setString(Entity::DATA_NPC_ACTIONS, json_encode($this->jsonSerialize()));
    }

    public function handleResponse(Player $player, $response): void {
        // todo
        // response is probably the button index??? test
        if($response === null) {
            // executed when closed???
            DialogFormStore::unregisterForm($this);
        } elseif(is_int($response)) {
            $this->buttons[$response]->executeSubmitListener($player); // todo test
        }
    }

    public function jsonSerialize(): array {
        return [
            "buttons" => array_map(function(Button $button) { return $button->jsonSerialize(); }, $this->buttons)
        ];
    }

}