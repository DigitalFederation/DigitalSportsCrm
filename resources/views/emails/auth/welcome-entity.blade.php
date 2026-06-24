@component('mail::message')
{{-- Portuguese Version --}}
# Bem-vindo ao {{ config('app.name') }}

Saudacoes, {{ $entityName }}!

Foi criada uma conta para a sua entidade.

Para gerir o perfil da sua entidade e explorar as funcionalidades da nossa plataforma, por favor defina a sua palavra-passe.

@component('mail::button', ['url' => $url])
Definir Palavra-passe
@endcomponent

Apos definir a sua palavra-passe, tera acesso completo ao seu painel de controlo.

Aguardamos a sua participacao ativa.

Obrigado por fazer parte do {{ config('app.name') }}. Se tiver alguma duvida, nao hesite em contactar-nos.

---

{{-- English Version --}}
# Welcome to {{ config('app.name') }}

Greetings, {{ $entityName }}!

An account has been created for your entity.

To manage your entity profile and explore the functionalities of our platform, please set your password.

@component('mail::button', ['url' => $url])
Set Your Password
@endcomponent

Once your password is set, you will have full access to your dashboard.

We look forward to your active participation.

Thank you for being a part of {{ config('app.name') }}. If you have any questions, feel free to contact us.

Com os melhores cumprimentos / Warm regards,<br>
{{ config('app.name') }}
@endcomponent
