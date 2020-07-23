# NpcDialog

### How to use 

First, you have to register the virion, you can do this during the `onEnable()` of your plugin main class.

```php
public function onEnable(): void {
    NpcDialog::register($this);
}
```