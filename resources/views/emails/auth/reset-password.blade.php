@component('mail::message')
{{-- Portuguese Version --}}
# Redefinir Palavra-passe

Ola,

Recebeu este email porque recebemos um pedido de redefinicao de palavra-passe para a sua conta.

@component('mail::button', ['url' => $url])
Redefinir Palavra-passe
@endcomponent

Este link de redefinicao de palavra-passe expira em {{ $count }} minutos.

Se nao solicitou a redefinicao de palavra-passe, nenhuma acao adicional e necessaria.

---

{{-- English Version --}}
# Reset Password

Hello,

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

This password reset link will expire in {{ $count }} minutes.

If you did not request a password reset, no further action is required.

Com os melhores cumprimentos / Best regards,<br>
{{ config('app.name') }}

@component('mail::subcopy')
**PT:** Se tiver problemas ao clicar no botao "Redefinir Palavra-passe", copie e cole o URL abaixo no seu navegador: [{{ $url }}]({{ $url }})

**EN:** If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: [{{ $url }}]({{ $url }})
@endcomponent
@endcomponent
