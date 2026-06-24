@component('mail::message')
{{-- Portuguese Version --}}
# Ative a Sua Conta {{ config('app.name') }}

Ola,

Esta e a sua conta para {{ $federationName }}.

Para ativar a sua conta e definir a sua palavra-passe, por favor clique no botao abaixo.

@component('mail::button', ['url' => $url])
Ativar e Definir Palavra-passe
@endcomponent

Este link e valido por 60 minutos. Se expirar, pode solicitar um novo a partir da pagina de inicio de sessao.

Uma vez ativada, tera acesso total ao painel de controlo da sua federacao e a todas as funcionalidades da nossa plataforma.

Se nao solicitou isto ou nao faz parte de uma federacao, nenhuma acao adicional e necessaria.

Obrigado por fazer parte do {{ config('app.name') }}. Se tiver alguma duvida, nao hesite em contactar-nos.

---

{{-- English Version --}}
# Activate Your {{ config('app.name') }} Account

Hello,

This is your account for {{ $federationName }}.

To activate your account and set your password, please click the button below.

@component('mail::button', ['url' => $url])
Activate and Set Password
@endcomponent

This link is valid for 60 minutes. If it expires, you can request a new one from the login page.

Once activated, you will have full access to your federation dashboard and all the features of our platform.

If you did not request this or are not part of a federation, no further action is required.

Thank you for being a part of {{ config('app.name') }}. If you have any questions, feel free to contact us.

Com os melhores cumprimentos / Warm regards,<br>
{{ config('app.name') }}
@endcomponent
