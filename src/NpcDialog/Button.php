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
use JsonSerializable;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

class Button implements JsonSerializable{

	private ?string $data = null;

	private int $mode = self::MODE_BUTTON;

	private const MODE_BUTTON = 0;//TODO use enum
	private const MODE_ON_CLOSE = 1;
	private const MODE_ON_OPEN = 2;

	private int $type = self::TYPE_COMMAND; // ????

	private const TYPE_URL = 0; //TODO use enum
	private const TYPE_COMMAND = 1;
	private const TYPE_INVALID = 2;

	private ?Closure $submitListener;

	public function __construct(private string $name = "", private string $command = "", ?Closure $submitListener = null){
		$this->setSubmitListener($submitListener);
	}

	public function getName() : string{
		return $this->name;
	}

	/** @return $this */
	public function setName(string $name) : self{
		$this->name = $name;
		return $this;
	}

	public function getCommand() : string{ return $this->command; }

	/** @return $this */
	public function setCommand(string $command) : self{
		$this->command = $command;
		return $this;
	}

	public function getMode() : int{ return $this->mode; }

	/** @return $this */
	public function setMode(int $mode = self::MODE_BUTTON) : self{
		$this->mode = $mode;
		return $this;
	}

	public function getType() : int{ return $this->type; }

	/** @return $this */
	public function setType(int $type = self::TYPE_COMMAND) : self{
		$this->type = $type;
		return $this;
	}

	public function getSubmitListener() : ?Closure{
		return $this->submitListener;
	}

	/** @return $this */
	public function setSubmitListener(?Closure $submitListener) : self{
		if($submitListener !== null){
			Utils::validateCallableSignature(function(Player $player){ }, $submitListener);
		}

		$this->submitListener = $submitListener;
		return $this;
	}

	public function executeSubmitListener(Player $player) : void{
		if($this->submitListener !== null){
			($this->submitListener)($player);
		}
	}

	public function jsonSerialize() : array{
		return [
			"button_name" => $this->name,//the name of the button is only set if mode is 0 (button)
			"data" => $this->data,//the data of the button appears to be null????
			"mode" => $this->mode,//0 = button, 1 = on close, 2 = on open
			"text" => $this->command,//the text in the command field
			"type" => $this->type//always 1 (command) when not education edition
		];
	}
}