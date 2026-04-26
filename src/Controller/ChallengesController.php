<?php
declare(strict_types=1);

namespace Altcha\Controller;

use Altcha\Service\ChallengeService;
use Cake\Controller\Controller;
use Cake\Core\Configure;

class ChallengesController extends Controller
{
    public function challenge()
    {
        $config = (array)Configure::read('Altcha', []);
        $service = new ChallengeService($config);
        $challenge = $service->generate();

        return $this->response
            ->withType('application/json')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withStringBody(json_encode($challenge));
    }
}
