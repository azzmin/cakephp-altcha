<?php
declare(strict_types=1);

namespace Altcha\Test\TestCase\Controller\Component;

use Altcha\Service\ChallengeService;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;

class AltchaComponentTest extends TestCase
{
    private function createComponent(ServerRequest $request): object
    {
        Configure::write('App.encoding', 'UTF-8');
        $controller = new Controller($request);

        return $controller->loadComponent('Altcha.Altcha');
    }

    public function testVerifyRejectsMissingData(): void
    {
        $request = new ServerRequest(['post' => []]);
        $component = $this->createComponent($request);

        $this->assertFalse($component->verify($request));
    }

    public function testVerifyRejectsEmptyString(): void
    {
        $request = new ServerRequest(['post' => ['altcha' => '']]);
        $component = $this->createComponent($request);

        $this->assertFalse($component->verify($request));
    }

    public function testVerifyRejectsInvalidPayload(): void
    {
        $request = new ServerRequest(['post' => ['altcha' => 'not-valid-base64!!!']]);
        $component = $this->createComponent($request);

        $this->assertFalse($component->verify($request));
    }

    public function testVerifyAcceptsValidSolution(): void
    {
        $service = new ChallengeService();
        $challenge = $service->generate();

        $number = null;
        for ($i = 0; $i <= $challenge['maxnumber']; $i++) {
            if (hash('sha256', $challenge['salt'] . $i) === $challenge['challenge']) {
                $number = $i;
                break;
            }
        }
        $this->assertNotNull($number);

        $solution = base64_encode(json_encode([
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => $number,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature'],
        ]));

        $request = new ServerRequest(['post' => ['altcha' => $solution]]);
        $component = $this->createComponent($request);

        $this->assertTrue($component->verify($request));
    }

    public function testVerifyUsesCustomFieldName(): void
    {
        $request = new ServerRequest(['post' => ['altcha' => 'x', 'custom' => '']]);
        $component = $this->createComponent($request);

        $this->assertFalse($component->verify($request, 'custom'));
    }

    public function testVerifyRejectsTamperedSolution(): void
    {
        $service = new ChallengeService();
        $challenge = $service->generate();

        $solution = base64_encode(json_encode([
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => 999999,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature'],
        ]));

        $request = new ServerRequest(['post' => ['altcha' => $solution]]);
        $component = $this->createComponent($request);

        $this->assertFalse($component->verify($request));
    }
}
