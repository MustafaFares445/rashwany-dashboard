<?php

namespace App\Services;

use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingsService
{
    private const DEFAULTS = [
        'hour_rounding_interval_minutes' => 30,
        'hour_rounding_threshold_minutes' => 15,
        'open_session_review_after_hours' => 14,
        'max_normal_billable_session_hours' => 8,
        'allow_check_in_with_due_amount' => false,
        'max_allowed_due_amount' => 0,
        'notify_admin_for_abnormal_sessions' => true,
        'notify_member_before_subscription_expiry_days' => 2,
        'default_currency' => 'USD',
        'auto_close_abnormal_sessions' => false,
    ];

    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();
        $fallback = array_key_exists($key, self::DEFAULTS) ? self::DEFAULTS[$key] : $default;

        if (! $setting) {
            return $fallback;
        }

        return $this->castValue($setting->type, $setting->value, $fallback);
    }

    public function getInt(string $key): int
    {
        return (int) $this->get($key, 0);
    }

    public function getDecimal(string $key): float
    {
        return (float) $this->get($key, 0.0);
    }

    public function getBool(string $key): bool
    {
        return (bool) $this->get($key, false);
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    public function getArray(string $key): array
    {
        $value = $this->get($key, []);

        return is_array($value) ? $value : [];
    }

    public function create(array $data, ?int $actorId = null, ?string $ipAddress = null): Setting
    {
        $payload = $this->normalizePayload($data);
        $setting = Setting::create($payload);

        $this->audit->log(
            action: 'setting_created',
            entityType: 'setting',
            entityId: $setting->id,
            newValues: $setting->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $setting;
    }

    public function update(Setting $setting, array $data, ?int $actorId = null, ?string $ipAddress = null): Setting
    {
        $before = $setting->replicate();
        $payload = $this->normalizePayload($data, $setting);

        $setting->update($payload);

        $this->audit->log(
            action: 'setting_updated',
            entityType: 'setting',
            entityId: $setting->id,
            oldValues: $before->toArray(),
            newValues: $setting->toArray(),
            actorId: $actorId,
            ipAddress: $ipAddress,
        );

        return $setting;
    }

    public function upsert(
        string $key,
        mixed $value,
        SettingType $type = SettingType::String,
        ?string $group = null,
        ?string $description = null,
        bool $isPublic = false,
    ): Setting {
        $setting = Setting::query()->firstOrNew(['key' => $key]);

        $payload = $this->normalizePayload([
            'key' => $key,
            'value' => $value,
            'type' => $type->value,
            'group' => $group ?? $setting->group,
            'description' => $description ?? $setting->description,
            'is_public' => $isPublic,
        ], $setting->exists ? $setting : null);

        $setting->fill($payload);
        $setting->save();

        return $setting;
    }

    private function castValue(SettingType $type, mixed $value, mixed $fallback): mixed
    {
        if ($value === null) {
            return $fallback;
        }

        return match ($type) {
            SettingType::Integer => (int) $value,
            SettingType::Decimal => (float) $value,
            SettingType::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            SettingType::Json => is_array($value) ? $value : json_decode((string) $value, true) ?? $fallback,
            SettingType::String => (string) $value,
        };
    }

    private function normalizePayload(array $data, ?Setting $setting = null): array
    {
        $payload = Arr::only($data, [
            'key',
            'value',
            'type',
            'group',
            'description',
            'is_public',
        ]);

        $rawType = $payload['type'] ?? $setting?->type?->value ?? $setting?->type ?? SettingType::String->value;
        $type = $rawType instanceof SettingType ? $rawType : SettingType::from((string) $rawType);

        $payload['type'] = $type->value;

        if (array_key_exists('value', $payload)) {
            $payload['value'] = $this->serializeValue($payload['value'], $type);
        }

        return $payload;
    }

    private function serializeValue(mixed $value, SettingType $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SettingType::Json => is_string($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR),
            SettingType::Boolean => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
