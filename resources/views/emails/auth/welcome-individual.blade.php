@component('mail::message')
{{-- Portuguese Version --}}
# Bem-vindo ao {{ config('app.name') }}

Ola {{ $individualName }},

Foi criada uma conta para si no {{ config('app.name') }}.

Por favor ative a sua conta e defina a sua propria palavra-passe clicando no link abaixo:

@component('mail::button', ['url' => $activationUrl])
Ativar Conta
@endcomponent

Ao clicar no link, sera solicitado que introduza o seu endereco de email. Recebera entao um email com um link para definir a sua palavra-passe.

Se tiver alguma duvida ou se nao solicitou esta conta, por favor contacte a sua federacao para assistencia.

---

{{-- English Version --}}
# Welcome to {{ config('app.name') }}

Hello {{ $individualName }},

An account has been created for you at {{ config('app.name') }}.

Please activate your account and set your own password by clicking the link below:

@component('mail::button', ['url' => $activationUrl])
Activate Account
@endcomponent

Upon clicking the link, you will be prompted to enter your email address. You will then receive an email with a link to set your password.

If you have any questions or if you did not request this account, please contact your federation for assistance.

Com os melhores cumprimentos / Warm regards,<br>
{{ config('app.name') }}
@endcomponent
