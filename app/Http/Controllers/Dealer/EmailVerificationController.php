<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Services\DealerEmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function show(Request $request)
    {
        $dealerId = (int) $request->session()->get('dealer_verification_id', 0);
        if (! $dealerId) {
            return redirect()->route('dealer.register');
        }

        $dealer = Dealer::query()->find($dealerId);
        if (! $dealer) {
            $request->session()->forget('dealer_verification_id');

            return redirect()->route('dealer.register');
        }

        if ($dealer->email_verified_at) {
            $request->session()->forget('dealer_verification_id');

            return redirect()->route('dealer.login')->with('success', 'E-posta doğrulamanız zaten tamamlanmış.');
        }

        $masked = $this->maskEmail($dealer->email);
        $nextAllowedAt = app(DealerEmailVerificationService::class)->nextAllowedSendAt($dealer);
        $cooldownSeconds = 0;
        if ($nextAllowedAt && $nextAllowedAt->isFuture()) {
            $cooldownSeconds = now()->diffInSeconds($nextAllowedAt);
        }

        return view('auth.dealer.verify', [
            'maskedEmail' => $masked,
            'cooldownSeconds' => $cooldownSeconds,
        ]);
    }

    public function verify(Request $request, DealerEmailVerificationService $service)
    {
        $dealerId = (int) $request->session()->get('dealer_verification_id', 0);
        if (! $dealerId) {
            return redirect()->route('dealer.register');
        }

        $dealer = Dealer::query()->findOrFail($dealerId);

        $key = "dealer:verify:attempts:{$dealer->id}";
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => ["Çok fazla deneme yapıldı. {$seconds} sn sonra tekrar deneyin."],
            ])->errorBag('verify');
        }
        RateLimiter::hit($key, 600);

        $validated = $request->validateWithBag('verify', [
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'code.required' => 'Doğrulama kodu gerekli.',
            'code.size' => 'Kod 6 haneli olmalı.',
            'code.regex' => 'Kod sadece rakam olmalı.',
        ]);

        $service->verify($dealer, $validated['code']);

        $dealer->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $request->session()->forget('dealer_verification_id');

        return redirect()->route('dealer.login')
            ->with('success', 'E-posta doğrulandı. Admin onayı sonrası giriş yapabilirsiniz.');
    }

    public function resend(Request $request, DealerEmailVerificationService $service)
    {
        $dealerId = (int) $request->session()->get('dealer_verification_id', 0);
        if (! $dealerId) {
            return redirect()->route('dealer.register');
        }

        $dealer = Dealer::query()->findOrFail($dealerId);
        if ($dealer->email_verified_at) {
            $request->session()->forget('dealer_verification_id');

            return redirect()->route('dealer.login')->with('success', 'E-posta doğrulamanız zaten tamamlanmış.');
        }

        $ip = (string) $request->ip();
        $payload = $service->sendCode($dealer, $ip);

        $message = 'Yeni doğrulama kodu e-postanıza gönderildi.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cooldown_seconds' => $payload['cooldown_seconds'],
            ]);
        }

        return redirect()->route('dealer.verify.show')->with('success', $message);
    }

    private function maskEmail(string $email): string
    {
        $email = trim($email);
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $local = trim($local);
        $domain = trim($domain);

        $localMasked = match (true) {
            strlen($local) <= 1 => '*',
            strlen($local) === 2 => substr($local, 0, 1).'*',
            default => substr($local, 0, 2).str_repeat('*', max(1, strlen($local) - 2)),
        };

        $domainParts = explode('.', $domain);
        $domainName = $domainParts[0] ?? $domain;
        $domainRest = implode('.', array_slice($domainParts, 1));
        $domainMasked = Str::substr($domainName, 0, 2).str_repeat('*', max(1, strlen($domainName) - 2));
        if ($domainRest) {
            $domainMasked .= '.'.$domainRest;
        }

        return $localMasked.'@'.$domainMasked;
    }
}

