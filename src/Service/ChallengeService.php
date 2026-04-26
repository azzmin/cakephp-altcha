<?php
declare(strict_types=1);

namespace Altcha\Service;

use Cake\Core\Configure;

class ChallengeService
{
    private string $hmacKey;
    private int $maxNumber;
    private int $saltLength;
    private string $algorithm;

    public function __construct(array $config = [])
    {
        $this->hmacKey = ($config['hmacKey'] ?? null) ?: Configure::read('Security.salt') ?: 'altcha-signing-key';
        $this->maxNumber = $config['maxNumber'] ?? 100000;
        $this->saltLength = $config['saltLength'] ?? 12;
        $this->algorithm = $config['algorithm'] ?? 'SHA-256';
    }

    public function generate(): array
    {
        $salt = $this->generateSalt();
        $secret = random_int(0, $this->maxNumber);
        $challenge = hash('sha256', $salt . $secret);

        $payload = [
            'algorithm' => $this->algorithm,
            'challenge' => $challenge,
            'maxnumber' => $this->maxNumber,
            'salt' => $salt,
            'saltlength' => $this->saltLength,
        ];

        if ($this->hmacKey !== '') {
            $payload['signature'] = hash_hmac('sha256', $challenge, $this->hmacKey);
        }

        return $payload;
    }

    public function verify(string $base64Payload): bool
    {
        if ($base64Payload === '') {
            return false;
        }

        $json = base64_decode($base64Payload, true);
        if ($json === false) {
            return false;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return false;
        }

        if (!isset($data['algorithm'], $data['challenge'], $data['number'], $data['salt'])) {
            return false;
        }

        if ($data['algorithm'] !== 'SHA-256') {
            return false;
        }

        $computed = hash('sha256', $data['salt'] . (int)$data['number']);
        if (!hash_equals($computed, $data['challenge'])) {
            return false;
        }

        if ($this->hmacKey !== '') {
            if (!isset($data['signature'])) {
                return false;
            }
            $expectedSig = hash_hmac('sha256', $data['challenge'], $this->hmacKey);
            if (!hash_equals($expectedSig, $data['signature'])) {
                return false;
            }
        }

        return true;
    }

    private function generateSalt(): string
    {
        $bytes = random_bytes((int)ceil($this->saltLength * 3 / 4));

        return substr(base64_encode($bytes), 0, $this->saltLength);
    }

    public function getMaxNumber(): int
    {
        return $this->maxNumber;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
}
