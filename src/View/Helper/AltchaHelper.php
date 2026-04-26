<?php
declare(strict_types=1);

namespace Altcha\View\Helper;

use Altcha\Service\ChallengeService;
use Cake\Core\Configure;
use Cake\View\Helper;

class AltchaHelper extends Helper
{
    private bool $scriptLoaded = false;

    public function widget(array $options = []): string
    {
        $config = (array)Configure::read('Altcha', []);
        $service = new ChallengeService($config);
        $challenge = $service->generate();

        $widgetAttrs = array_merge([
            'challengejson' => json_encode($challenge),
        ], $options);

        $html = '';
        if (!$this->scriptLoaded) {
            $html .= $this->script($config);
            $this->scriptLoaded = true;
        }

        $html .= '<altcha-widget';
        foreach ($widgetAttrs as $key => $value) {
            if ($value === true) {
                $html .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            } elseif ($value !== false) {
                $html .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
                    . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        $html .= '></altcha-widget>';

        return $html;
    }

    public function script(array $config = []): string
    {
        $jsUrl = $config['jsUrl'] ?? 'https://cdn.jsdelivr.net/npm/altcha@latest/dist/altcha.js';

        return '<script src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '" type="module"></script>';
    }
}
