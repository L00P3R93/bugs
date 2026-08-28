<div style="background-color:#0a0a0a;padding:32px 16px;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:520px;margin:0 auto;background-color:#1a1a1a;border:1px solid rgba(245,197,66,0.3);border-radius:12px;padding:32px;">
        <p style="margin:0 0 8px;font-size:13px;letter-spacing:2px;color:#f5c542;text-transform:uppercase;">{{ $appName }} · Security</p>

        <h1 style="margin:0 0 16px;font-size:22px;color:#f5f5f0;">{{ __('Security alert') }}</h1>

        <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#e5e5e5;">
            {{ __('Hi :name,', ['name' => $user->name]) }}
        </p>

        <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#e5e5e5;">
            {{ __(':change on :when.', ['change' => $change, 'when' => $when]) }}
        </p>

        <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#6b6b6b;">
            {{ __("If this was you, no action is needed. If you don't recognize this change, please sign in to your account, secure it immediately, and contact support.") }}
        </p>

        <p style="margin:0;font-size:12px;color:#6b6b6b;">
            {{ __('You are receiving this email because security settings changed for your account.') }}
        </p>
    </div>
</div>
