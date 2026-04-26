<?php
declare(strict_types=1);

namespace Altcha\Controller\Component;

use Altcha\Service\ChallengeService;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;

class AltchaComponent extends Component
{
    public function verify(ServerRequest $request, string $fieldName = 'altcha'): bool
    {
        $payload = $request->getData($fieldName);
        if (!is_string($payload) || $payload === '') {
            return false;
        }

        $config = (array)Configure::read('Altcha', []);
        $service = new ChallengeService($config);

        return $service->verify($payload);
    }
}
