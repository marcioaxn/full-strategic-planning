<x-mail::message>
# Boas-vindas ao Sistema PEI, {{ $nome }}!

É com satisfação que confirmamos a criação do seu acesso ao **Sistema de Planejamento Estratégico Integrado (PEI)** do Ministério da Integração e do Desenvolvimento Regional (MIDR).

A partir de agora, você terá em um único ambiente os instrumentos para conduzir a estratégia institucional: ciclos de planejamento, identidade estratégica, objetivos, indicadores de desempenho, planos de ação, gestão de riscos e relatórios gerenciais.

## Falta apenas um passo: criar a sua senha

Por questões de segurança, **a sua senha ainda não foi definida**. Para concluir o primeiro acesso, basta clicar no botão abaixo e cadastrar uma senha pessoal:

<x-mail::button :url="$url" color="primary">
Cadastrar minha senha de acesso
</x-mail::button>

<x-mail::panel>
**Atenção ao prazo:** por segurança, este link é válido por **{{ $minutos }} minutos** a partir do envio deste e-mail. Caso ele expire, solicite um novo convite ao administrador do sistema ou utilize a opção **"Esqueci minha senha"** na tela de acesso.
</x-mail::panel>

**Recomendações de segurança**

- Crie uma senha forte: com letras maiúsculas e minúsculas, números e ao menos um caractere especial.
- Não reutilize senhas de outros serviços e não as compartilhe com terceiros.
- O sistema nunca solicitará a sua senha por e-mail, telefone ou mensagem.

Se você **não reconhece** este convite ou não esperava recebê-lo, nenhuma providência é necessária — basta desconsiderar esta mensagem com segurança.

Estamos à disposição para apoiar você a transformar o planejamento em resultados concretos.

Atenciosamente,<br>
**Equipe do Sistema PEI** — MIDR

<x-slot:subcopy>
Caso o botão acima não funcione, copie e cole o endereço a seguir no seu navegador:
[{{ $url }}]({{ $url }})

Esta é uma mensagem automática — por favor, não responda a este e-mail.
</x-slot:subcopy>
</x-mail::message>
