# cakephp-altcha

Altcha proof-of-work spam protection for CakePHP 5. Privacy-friendly, no external services, no tracking.

Uses [Altcha](https://altcha.org/) to generate SHA-256 challenges that are solved client-side. No CAPTCHA images, no Google dependencies.

## Install

```bash
composer require azzmin/cakephp-altcha
```

## Setup

**1. Load the plugin** in `src/Application.php`:

```php
$this->addPlugin('Altcha');
```

**2. In your controller** load the component and helper:

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Altcha.Altcha');
    $this->viewBuilder()->addHelper('Altcha.Altcha');
}
```

**3. Verify on POST** in your action, before processing the form:

```php
if ($this->request->is('post')) {
    if (!$this->Altcha->verify($this->request)) {
        $this->Flash->error('Please complete the verification.');
        return null;
    }

    // process form...
}
```

**4. Render the widget** in your template, before the submit button:

```php
<?= $this->Altcha->widget() ?>
```

That's it. No database, no routes, no configuration required.

## Options

Pass an array to `widget()` to customise:

```php
<?= $this->Altcha->widget(['hidelogo' => true]) ?>
```

| Option | Type | Description |
| --- | --- | --- |
| `hidelogo` | `true` | Hide the Altcha logo |
| `hidelabel` | `true` | Hide the "I'm not a robot" label |
| `name` | `string` | Hidden input name (default: `altcha`) |
| `auto` | `string` | Auto-solve mode: `onfocus`, `onload`, `onsubmit` |

If you change `name`, pass the same value to verify:

```php
$this->Altcha->verify($this->request, 'my_field_name');
```

## Configuration

All optional. Defaults work out of the box using `Security.salt` from `app_local.php`.

Add to `config/app_local.php` to override:

```php
'Altcha' => [
    'hmacKey' => 'your-custom-key',    // defaults to Security.salt
    'maxNumber' => 100000,              // higher = harder for bots
    'saltLength' => 12,
    'jsUrl' => 'https://cdn.jsdelivr.net/npm/altcha@latest/dist/altcha.js',
],
```

## How it works

1. Server generates a SHA-256 challenge with a HMAC signature
2. Client solves the proof-of-work in the browser (finds the nonce)
3. Solution is submitted as a hidden form field
4. Server verifies the hash and HMAC signature

No data sent to third parties. All computation happens in the browser.

## Requirements

- PHP 8.1+
- CakePHP 5.0+

## License

MIT
