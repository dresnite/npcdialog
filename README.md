# NpcDialog

### Download

The compiled phar is available [on poggit](https://poggit.pmmp.io/ci/GiantQuartz/NpcDialog/NpcDialog)

### How to use

First, you have to register the virion, you can do this during the `onEnable()` of your plugin main class.

```php
public function onEnable(): void {
    NpcDialog::register($this);
}
```

Then you have to spawn or get the object of the entity you want to have the dialog form. For
this example, I will spawn a zombie using PocketMine built-in methods.

 ```php
$nbt = Entity::createBaseNBT($player, null, $player->yaw, $player->pitch);
$entity = Entity::createEntity("Zombie", $player->level, $nbt);
$entity->spawnToAll();
 
$entity->setNameTag("Jerry The Zombie!");
 ```

Finally you will have to create the form and pair it with the entity.

```php
//Add a new form with the text "This is the dialog text"
$form = new DialogForm("This is the dialog text");
//Add a button with the text "Hi" and the optional command "say Hi!!" and a listener for when the button is clicked
$form->addButton("Hi", "say Hi!!", function(Player $player) {
    $player->sendMessage("Hi!!");
});
//Set a listener for when the form is opened
$form->setOpenListener(function(Player $player) {
    $player->sendMessage("You opened the form!");
});
//Set a listener for when the form is closed
$form->setCloseListener(function(Player $player) {
    $player->sendMessage("You closed the form!");
});
//Pair the form with the entity so it will show when the entity is right-clicked
$form->pairWithEntity($entity);
```

This can be trimmed down to a single line:

```php
(new DialogForm("This is the dialog text", function(Player $player){ $player->sendMessage("You opened the form!"); }, function(Player $player){ $player->sendMessage("You closed the form!"); }))->addButton("Hi", "say Hii!!", function(Player $player){ $player->sendMessage("Hi!!"); })->pairWithEntity($entity);
```

The result of this example would be an entity showing this when it's right-clicked (or hold in the mobile versions):

![Example](https://i.imgur.com/468mQKF.png)

You can also force the form to show by using the `open(Player $player)` method.

```php
$form->open($player);
```

or close it by using the `close(Player $player)` method.

```php
$form->close($player);
```

When the user clicks on a button, it's `$submitListener` listener will be called. The command will not be executed by
default, you have to do it yourself (the command parameter does not need to be set). Clicking a button will force the
form to close in order to prevent the user from being locked in the form (the client usually will request the form to be
closed when the user clicks on a button, but better be safe).