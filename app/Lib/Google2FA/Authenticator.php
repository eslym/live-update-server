<?php

namespace App\Lib\Google2FA;

use App\Models\User;
use Illuminate\Support\Carbon;
use PragmaRX\Google2FA\Google2FA;

/**
 * @method static string generateSecret()
 * @method static bool verifySession(string $otp)
 * @method static bool verify(string $otp, ?string $secret = null)
 * @method static int isExpired()
 * @method static void forceSession()
 * @method static void clearSession()
 * @method static string getQRCodeUrl(string $company, string $holder, string $secret)
 * @method static string getCurrentOTP(string $secret)
 * @method static array<string> generateRecoveryCodes(int $n = 10)
 */
final class Authenticator
{
    public const NOT_EXPIRED = 0;
    public const EXPIRED = 1;
    public const NO_SESSION = -1;

    public function __construct(private readonly Google2FA $google2fa, private readonly int $keyLength, private readonly int $keepAlive)
    {
    }

    private function _generateSecret(): string
    {
        return $this->google2fa->generateSecretKey($this->keyLength);
    }

    private function _verifySession(string $otp): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user instanceof User) {
            return false;
        }

        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            if (!$user->recovery_codes()->where('code', $otp)->count()) {
                return false;
            }
            $user->recovery_codes()->where('code', $otp)->delete();
            session()->put('google2fa_timestamp', time());
            return true;
        }

        $secret = $user->google2fa_secret;
        if (!$secret) {
            return false;
        }

        $last = floor(session()->get('google2fa_timestamp', 0) / $this->google2fa->getKeyRegeneration());
        $new = $this->google2fa->verifyKeyNewer($secret, $otp, $last);
        $new *= $this->google2fa->getKeyRegeneration();

        if (!$new) {
            return false;
        }
        session()->put('google2fa_timestamp', $new);
        return true;
    }

    private function _verify(string $otp, string $secret = null): bool
    {
        return $this->google2fa->verifyKey($secret, $otp);
    }

    private function _isExpired(): int
    {
        $last = session()->get('google2fa_timestamp', 0);
        if ($last === 0) {
            return -1;
        }
        $ts = Carbon::createFromTimestamp($last);
        return (int)$ts->addMinutes($this->keepAlive)->isPast();
    }

    private function _forceSession(): void
    {
        session()->put('google2fa_timestamp', time());
    }

    private function _clearSession(): void
    {
        session()->forget('google2fa_timestamp');
    }

    private function _getQRCodeUrl(string $company, string $holder, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($company, $holder, $secret);
    }

    /**
     * @return array<string>
     */
    private function _generateRecoveryCodes($n = 10): array
    {
        $bytes = openssl_random_pseudo_bytes(4 * $n);
        $integers = unpack('N*', $bytes);
        $codes = [];
        foreach ($integers as $integer) {
            $code = str_pad((string)($integer % 1_000_000_000), 9, '0', STR_PAD_LEFT);
            $code = preg_replace('/^(\d{3})(\d{3})(\d{3})$/', '$1-$2-$3', $code);
            if (!in_array($code, $codes)) {
                $codes[] = $code;
            }
        }
        $count = count($codes);
        if ($count < $n) {
            $codes = [...$codes, ...$this->generateRecoveryCodes($n - $count)];
        }
        return $codes;
    }

    public function _getCurrentOTP(string $secret): string
    {
        return $this->google2fa->getCurrentOtp($secret);
    }

    public static function __callStatic($name, $arguments)
    {
        return app(self::class)->{'_' . $name}(...$arguments);
    }
}
