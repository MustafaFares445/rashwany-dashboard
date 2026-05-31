<?php

namespace App\Services;

use App\Models\QrCode;
use Illuminate\Support\Str;

class QrCodeService
{
    public function create(array $data): QrCode
    {
        $token = $data['token'] ?? null;
        if (! is_string($token) || $token === '') {
            $token = Str::random(40);
        }

        $data['token_hash'] = $this->hashToken($token);
        unset($data['token']);

        return QrCode::create($data);
    }

    public function update(QrCode $qrCode, array $data): QrCode
    {
        if (isset($data['token']) && is_string($data['token']) && $data['token'] !== '') {
            $data['token_hash'] = $this->hashToken($data['token']);
        }

        unset($data['token']);

        $qrCode->update($data);

        return $qrCode;
    }

    public function findByToken(string $token): ?QrCode
    {
        return QrCode::query()
            ->where('token_hash', $this->hashToken($token))
            ->first();
    }

    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
