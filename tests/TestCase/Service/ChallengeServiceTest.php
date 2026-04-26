<?php
declare(strict_types=1);

namespace Altcha\Test\TestCase\Service;

use Altcha\Service\ChallengeService;
use PHPUnit\Framework\TestCase;

class ChallengeServiceTest extends TestCase
{
    public function testGenerateReturnsRequiredFields(): void
    {
        $service = new ChallengeService();
        $result = $service->generate();

        $this->assertArrayHasKey('algorithm', $result);
        $this->assertArrayHasKey('challenge', $result);
        $this->assertArrayHasKey('maxnumber', $result);
        $this->assertArrayHasKey('salt', $result);
        $this->assertArrayHasKey('saltlength', $result);
        $this->assertSame('SHA-256', $result['algorithm']);
        $this->assertSame(100000, $result['maxnumber']);
        $this->assertSame(12, $result['saltlength']);
    }

    public function testGenerateAlwaysIncludesSignature(): void
    {
        $service = new ChallengeService();
        $result = $service->generate();

        $this->assertArrayHasKey('signature', $result);
        $this->assertSame(64, strlen($result['signature']));
    }

    public function testGenerateWithHmacKeyIncludesSignature(): void
    {
        $service = new ChallengeService(['hmacKey' => 'test-secret']);
        $result = $service->generate();

        $this->assertArrayHasKey('signature', $result);
        $this->assertSame(64, strlen($result['signature']));
    }

    public function testVerifyAcceptsValidSolution(): void
    {
        $service = new ChallengeService();
        $challenge = $service->generate();

        $number = $this->solveChallenge($challenge['salt'], $challenge['challenge'], $challenge['maxnumber']);

        $solution = [
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => $number,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature'],
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertTrue($service->verify($payload));
    }

    public function testVerifyRejectsWrongNumber(): void
    {
        $service = new ChallengeService();
        $challenge = $service->generate();

        $solution = [
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => 999999,
            'salt' => $challenge['salt'],
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertFalse($service->verify($payload));
    }

    public function testVerifyRejectsEmptyPayload(): void
    {
        $service = new ChallengeService();
        $this->assertFalse($service->verify(''));
    }

    public function testVerifyRejectsInvalidBase64(): void
    {
        $service = new ChallengeService();
        $this->assertFalse($service->verify('!!!not-base64!!!'));
    }

    public function testVerifyRejectsInvalidJson(): void
    {
        $service = new ChallengeService();
        $this->assertFalse($service->verify(base64_encode('not json')));
    }

    public function testVerifyRejectsMissingFields(): void
    {
        $service = new ChallengeService();
        $this->assertFalse($service->verify(base64_encode(json_encode(['algorithm' => 'SHA-256']))));
    }

    public function testVerifyRejectsWrongAlgorithm(): void
    {
        $service = new ChallengeService();
        $challenge = $service->generate();

        $solution = [
            'algorithm' => 'SHA-512',
            'challenge' => $challenge['challenge'],
            'number' => 0,
            'salt' => $challenge['salt'],
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertFalse($service->verify($payload));
    }

    public function testVerifyWithHmacRejectsMissingSignature(): void
    {
        $service = new ChallengeService(['hmacKey' => 'secret']);
        $challenge = $service->generate();

        $solution = [
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => 0,
            'salt' => $challenge['salt'],
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertFalse($service->verify($payload));
    }

    public function testVerifyWithHmacRejectsWrongSignature(): void
    {
        $service = new ChallengeService(['hmacKey' => 'secret']);
        $challenge = $service->generate();

        $solution = [
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => 0,
            'salt' => $challenge['salt'],
            'signature' => 'wrong-signature',
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertFalse($service->verify($payload));
    }

    public function testVerifyWithHmacAcceptsValidSolution(): void
    {
        $service = new ChallengeService(['hmacKey' => 'secret']);
        $challenge = $service->generate();

        $number = $this->solveChallenge($challenge['salt'], $challenge['challenge'], $challenge['maxnumber']);

        $solution = [
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => $number,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature'],
        ];
        $payload = base64_encode(json_encode($solution));

        $this->assertTrue($service->verify($payload));
    }

    public function testCustomMaxNumber(): void
    {
        $service = new ChallengeService(['maxNumber' => 500]);
        $this->assertSame(500, $service->getMaxNumber());
    }

    public function testCustomAlgorithm(): void
    {
        $service = new ChallengeService(['algorithm' => 'SHA-256']);
        $this->assertSame('SHA-256', $service->getAlgorithm());
    }

    private function solveChallenge(string $salt, string $challenge, int $maxNumber): ?int
    {
        for ($i = 0; $i <= $maxNumber; $i++) {
            if (hash('sha256', $salt . $i) === $challenge) {
                return $i;
            }
        }

        return null;
    }
}
