@component('mail::message')
{{-- Portuguese Version --}}
# Bem-vindo ao {{ config('app.name') }}

Saudacoes, {{ $userName }}!

A {{ config('branding.primary.short_name', 'DF') }} criou uma conta para si na plataforma.

Para completar a configuracao da sua conta e obter acesso as funcionalidades exclusivas, por favor defina a sua palavra-passe.

@component('mail::button', ['url' => $url])
Definir Palavra-passe
@endcomponent

Apos definir a sua palavra-passe, podera iniciar sessao e explorar as funcionalidades da nossa plataforma.

Aguardamos a sua participacao ativa.

---

{{-- English Version --}}
# Welcome to {{ config('app.name') }}

Greetings, {{ $userName }}!

{{ config('branding.primary.short_name', 'DF') }} has created an account for you on the platform.

To complete your account setup and gain access to exclusive features, please set your password.

@component('mail::button', ['url' => $url])
Set Your Password
@endcomponent

After your password is set, you will be able to log in and explore the functionalities of our platform.

We look forward to your active involvement.

Com os melhores cumprimentos / Warm regards,<br>
{{ config('app.name') }}
@endcomponent
