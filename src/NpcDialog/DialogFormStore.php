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
use pocketmine\entity\Entity;

class DialogFormStore {

    /** @var DialogForm[] */
    static private $forms = [];

    static public function getFormByEntity(Entity $entity): ?DialogForm {
        foreach(self::$forms as $form) {
            if($form->getEntity() === $entity) {
                return $form;
            }
        }
        return null;
    }

    static public function registerForm(DialogForm $form): void {
        if(in_array($form, self::$forms)) {
            throw new InvalidArgumentException("Trying to overwrite an already registered npc form");
        }
        self::$forms[] = $form;
    }

    static public function unregisterForm(DialogForm $form): void {
        if(($key = array_search($form, self::$forms)) !== false) {
            unset(self::$forms[$key]);
        } else {
            throw new InvalidArgumentException("Tried to unregister a dialog form that wasn't registered");
        }
    }

}