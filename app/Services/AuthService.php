<?php

namespace App\Services;

/**
 * AuthService
 *
 * Centralises email-domain validation so that Developer Mode and Production
 * Mode rules apply uniformly to both manual and Google authentication.
 *
 * Configuration (in .env):
 *   DEVELOPER_MODE=true          — allow any email
 *   DEVELOPER_MODE=false         — restrict to ALLOWED_EMAIL_DOMAIN
 *   ALLOWED_EMAIL_DOMAIN=deped.gov.ph
 */
class AuthService
{
    protected bool $developerMode;
    protected string $allowedDomain;

    public function __construct()
    {
        $this->developerMode = (bool) config('services.auth.developer_mode', true);
        $this->allowedDomain = strtolower(config('services.auth.allowed_email_domain', 'deped.gov.ph'));
    }

    /**
     * Determine whether the given email address is permitted to authenticate.
     * Developer mode ? any email. Production ? must match ALLOWED_EMAIL_DOMAIN.
     */
    public function isEmailAllowed(string $email): bool
    {
        if ($this->developerMode) {
            return true;
        }
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return $domain === $this->allowedDomain;
    }

    /** Human-readable error message shown when an unauthorised email is used. */
    public function unauthorizedMessage(): string
    {
        return "Only authorized {$this->allowedDomain} email accounts can access this system.";
    }

    public function isDeveloperMode(): bool { return $this->developerMode; }
    public function allowedDomain(): string { return $this->allowedDomain; }
}
