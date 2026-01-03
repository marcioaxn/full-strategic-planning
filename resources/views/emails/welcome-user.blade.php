<x-mail::message>
# Bem-vindo ao SPS!

Olá **{{ $user->name }}**,

Sua conta foi criada no Strategic Planning System.

Abaixo estão suas credenciais de acesso:

<x-mail::panel>
**E-mail:** {{ $user->email }}

**Senha:** {{ $password }}
</x-mail::panel>

<x-mail::button :url="$loginUrl">
Acessar o Sistema
</x-mail::button>

**Importante:** Por motivos de segurança, recomendamos que você altere sua senha no primeiro acesso.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
