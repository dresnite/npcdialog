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

class DialogFormStore {

    /** @var DialogForm[] */
    static private $forms = [];

    static public function getFormByEntityId(int $entityId): ?DialogForm {
        foreach(self::$forms as $form) {
            if($form->hasEntity() and $form->getEntity()->getId() === $entityId) {
                return $form;
            }
        }
        return null;
    }

    static public function registerForm(DialogForm $form, bool $overwrite = false): void {
        if(in_array($form, self::$forms) and !$overwrite) {
            throw new InvalidArgumentException("Trying to overwrite an already registered npc form");
        }
        self::$forms[] = $form;
    }

}