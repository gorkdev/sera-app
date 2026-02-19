<?php

namespace App\Services;

use App\Mail\DealerVerificationCodeMail;
use App\Models\Dealer;
use App\Models\DealerEmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DealerEmailVerificationService
{
    public const CODE_TTL_MINUTES = 10;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_SEND_PER_HOUR = 5;
    public const MAX_VERIFY_ATTEMPTS = 10;

    public function sendCode(Dealer $dealer, string $ip): array
    {
        $this->ensureNotThrottled($dealer, $ip);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();
        $expiresAt = $now->copy()->addMinutes(self::CODE_TTL_MINUTES);
        $hash = hash_hmac('sha256', $code, (string) config('app.key'));

        $verification = DealerEmailVerification::query()->firstOrNew([
            'dealer_id' => $dealer->id,
        ]);

        $verification->fill([
            'code_hash' => $hash,
            'expires_at' => $expiresAt,
            'last_sent_at' => $now,
            'attempts' => 0,
            'verified_at' => null,
        ]);
        $verification->send_count = (int) ($verification->send_count ?? 0) + 1;
        $verification->save();

        Mail::to($dealer->email)->send(new DealerVerificationCodeMail($code, self::CODE_TTL_MINUTES));

        return [
            'expires_at' => $expiresAt,
            'cooldown_seconds' => self::RESEND_COOLDOWN_SECONDS,
        ];
    }

    public function verify(Dealer $dealer, string $code): bool
    {
        $verification = DealerEmailVerification::query()
            ->where('dealer_id', $dealer->id)
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => ['Doğrulama kodu bulunamadı. Lütfen tekrar gönderin.'],
            ])->errorBag('verify');
        }

        if ($verification->verified_at) {
            return true;
        }

        if ($verification->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Kodun süresi doldu. Lütfen tekrar gönderin.'],
            ])->errorBag('verify');
        }

        if ($verification->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => ['Çok fazla deneme yapıldı. Lütfen yeni kod isteyin.'],
            ])->errorBag('verify');
        }

        $normalized = preg_replace('/\D+/', '', $code);
        $normalized = Str::substr((string) $normalized, 0, 6);

        $hash = hash_hmac('sha256', $normalized, (string) config('app.key'));
        if (! hash_equals($verification->code_hash, $hash)) {
            $verification->increment('attempts');
            throw ValidationException::withMessages([
                'code' => ['Kod hatalı. Lütfen tekrar deneyin.'],
            ])->errorBag('verify');
        }

        $verification->update([
            'verified_at' => now(),
        ]);

        return true;
    }

    public function nextAllowedSendAt(Dealer $dealer): ?\Illuminate\Support\Carbon
    {
        $verification = DealerEmailVerification::query()->where('dealer_id', $dealer->id)->first();
        if (! $verification?->last_sent_at) {
            return null;
        }

        return $verification->last_sent_at->copy()->addSeconds(self::RESEND_COOLDOWN_SECONDS);
    }

    private function ensureNotThrottled(Dealer $dealer, string $ip): void
    {
        $cooldownKey = "dealer:verify:cooldown:{$dealer->id}";
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);
            throw ValidationException::withMessages([
                'resend' => ["Lütfen {$seconds} sn sonra tekrar deneyin."],
            ])->errorBag('verify');
        }

        $hourKey = "dealer:verify:hour:{$dealer->id}";
        if (RateLimiter::tooManyAttempts($hourKey, self::MAX_SEND_PER_HOUR)) {
            throw ValidationException::withMessages([
                'resend' => ['Çok fazla kod istendi. Lütfen daha sonra tekrar deneyin.'],
            ])->errorBag('verify');
        }

        $ipKey = "dealer:verify:ip:{$ip}";
        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            throw ValidationException::withMessages([
                'resend' => ['Çok fazla istek algılandı. Lütfen daha sonra tekrar deneyin.'],
            ])->errorBag('verify');
        }

        RateLimiter::hit($cooldownKey, self::RESEND_COOLDOWN_SECONDS);
        RateLimiter::hit($hourKey, 3600);
        RateLimiter::hit($ipKey, 3600);
    }
}

