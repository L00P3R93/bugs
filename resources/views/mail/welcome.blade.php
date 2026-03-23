<x-mail::message>
# Welcome to {{ $appName }}, {{ $user->name }}!

Your email has been verified and your accounts are ready. Use the credentials below to log in to both platforms.

---

## {{ $appName }} (Kadi Bugs)

<x-mail::button :url="$appUrl">
Log in to Kadi Bugs
</x-mail::button>

| Field    | Value                   |
|----------|-------------------------|
| Email    | {{ $user->email }}      |
| Password | {{ $kadiPlayPassword ?? '(use the password you registered with)' }} |
| Account  | {{ $user->account_no }} |

---

## Kadi Play

| Field    | Value                   |
|----------|-------------------------|
| Email    | {{ $user->email }}      |
| Password | {{ $kadiPlayPassword ?? '(use the password you registered with)' }} |
| Phone    | {{ $user->phone }}      |

> **Note:** Both platforms use the same password you registered with.

---

If you did not create this account, please contact support immediately.

Thanks,<br>
{{ $appName }} Team
</x-mail::message>
