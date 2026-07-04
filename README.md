# 📦 ReservaJá — Sistema de Reservas e Empréstimos

Sistema web para catalogar e reservar recursos escolares (laboratórios,
projetores, kits de robótica, quadras, etc.), desenvolvido em **PHP orientado
a objetos**, **HTML** e **CSS**, sem frameworks — projeto acadêmico da
disciplina de Fábrica de Software (Grupo A / Projeto "Sistema de Reservas e
Empréstimos").

O cliente (Grupo B) pediu algo **simples de usar, porém profissional**: o
usuário comum apenas escolhe um recurso e reserva, sem burocracia; o
administrador cuida de todo o cadastro (categorias, setores e recursos).

---

## ✨ Funcionalidades

### Área do usuário (aluno/professor)
- Cadastro de conta e login
- Catálogo de recursos com foto, filtrável por categoria
- Página de detalhe do recurso
- **Reserva direta e imediata, por turno** (Manhã / Tarde / Noite) — sem etapa
  de aprovação: o usuário escolhe a data e o turno e a reserva já é confirmada
- **Disponibilidade visível para todos**: ao escolher uma data, os 3 turnos
  aparecem marcados como "Disponível" ou "Indisponível" em tempo real — assim
  que alguém reserva um turno, ele passa a aparecer como indisponível tanto
  para quem reservou quanto para qualquer outro usuário que consultar aquele
  recurso naquela data
- Tela "Minhas reservas", com opção de cancelar (o turno cancelado volta a
  ficar disponível imediatamente para todos)

### Área do administrador
- Dashboard com contadores (recursos catalogados, disponíveis, categorias,
  reservas ativas)
- CRUD de **Categorias** (tabela auxiliar)
- CRUD de **Setores responsáveis** (tabela auxiliar)
- CRUD de **Recursos** (cadastro principal, com upload de foto, ligado a
  Categoria e Setor)
- Visualização e cancelamento de qualquer reserva do sistema

### API
- `api/api_recursos.php` — endpoint JSON consumido pelo aplicativo Android,
  com filtro opcional por categoria ou por ID do recurso

---

## 🗂️ Estrutura de pastas

```
sistema-reservas/
├── admin/                 # Páginas da área administrativa
│   ├── _header.php / _footer.php   (layout compartilhado)
│   ├── dashboard.php
│   ├── categorias.php
│   ├── setores.php
│   ├── recursos.php
│   └── reservas.php
├── user/                  # Páginas da área do usuário comum
│   ├── _header.php / _footer.php
│   ├── catalogo.php
│   ├── reservar.php
│   └── minhas_reservas.php
├── classes/                # Classes PHP (POO)
│   ├── Database.php        # Conexão PDO (Singleton)
│   ├── Auth.php             # Sessão / controle de acesso
│   ├── Usuario.php
│   ├── Categoria.php
│   ├── Setor.php
│   ├── Recurso.php
│   └── Reserva.php
├── api/
│   └── api_recursos.php    # Endpoint JSON para o app Android
├── config/
│   └── config.php          # Credenciais do banco e constantes gerais
├── css/
│   └── style.css           # Design system completo (cores, botões, cards...)
├── database/
│   └── schema.sql           # Script de criação do banco + dados iniciais
├── docs/
│   ├── diagrama_classes.svg      # Diagrama de classes (UML)
│   └── diagrama_casos_de_uso.svg # Diagrama de casos de uso (UML)
├── uploads/                 # Fotos enviadas pelo admin (gerado em tempo de uso)
├── index.php                # Login
├── cadastro.php             # Cadastro de novo usuário
└── logout.php
```

---

## 🚀 Como colocar no ar (XAMPP / WAMP / Laragon)

1. **Copie a pasta** `sistema-reservas` para a pasta pública do seu servidor
   (ex: `htdocs/` no XAMPP).

2. **Crie o banco de dados**: abra o phpMyAdmin (ou o cliente MySQL de sua
   preferência) e importe o arquivo `database/schema.sql`. Ele cria o banco
   `sistema_reservas`, todas as tabelas e alguns dados iniciais (categorias,
   setores e um usuário administrador).

3. **Ajuste as credenciais**, se necessário, em `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_reservas');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
   Ajuste também `APP_URL` para o endereço real do projeto (usado para gerar
   a URL das fotos na API JSON).

4. **Dê permissão de escrita** à pasta `uploads/` (é onde as fotos dos
   recursos são salvas).

5. **Acesse** `http://localhost/sistema-reservas/` no navegador.

### 🔑 Login padrão do administrador
O script `schema.sql` já cria um administrador para você testar:

| Campo | Valor |
|---|---|
| E-mail | `admin@escola.com` |
| Senha | `admin123` |

> Recomenda-se alterar essa senha (ou criar outro admin e remover este) antes
> de usar em produção — para isso, cadastre um usuário normal pela tela de
> cadastro e depois altere manualmente o campo `tipo` para `admin` na tabela
> `usuarios`.

---

## 🧱 Arquitetura e decisões técnicas

- **Orientado a objetos**: cada entidade (`Usuario`, `Categoria`, `Setor`,
  `Recurso`, `Reserva`) é uma classe com atributos privados, getters/setters
  e métodos de negócio (CRUD). A classe `Database` implementa o padrão
  **Singleton** para fornecer uma única conexão PDO à aplicação, e `Auth`
  centraliza a lógica de sessão/login.
- **Sem etapa de aprovação, reserva por turno**: por pedido do cliente, a
  reserva é confirmada no mesmo instante em que o usuário a solicita. O
  sistema não trabalha com horário livre, e sim com 3 turnos fixos por dia
  (Manhã, Tarde, Noite). Assim que um turno é reservado para um recurso em
  uma data, ele aparece como **indisponível** para qualquer pessoa — o
  próprio usuário e os demais — que consultar aquele recurso naquela data,
  até que a reserva seja cancelada.
- **Separação de responsabilidades**: área pública (`index.php`,
  `cadastro.php`), área do usuário (`user/`) e área administrativa (`admin/`)
  ficam em pastas separadas, cada uma protegida por `Auth::exigirLogin()` ou
  `Auth::exigirAdmin()`.
- **Design**: paleta institucional (verde-petróleo + âmbar), tipografia
  Poppins/Inter, cartões com sombra suave, selos de status e layout
  responsivo (menu lateral colapsa em telas pequenas).
- **API JSON**: pensada para ser consumida pelo app Android do Projeto A,
  devolvendo a URL completa da foto de cada recurso.

---

## 📐 Diagramas (nível profissional)

Na pasta `docs/` você encontra, em SVG (abra em qualquer navegador):

- **`diagrama_classes.svg`** — todas as classes do sistema com atributos e
  métodos, relações de associação (multiplicidade 1 / 0..*) e dependências
  (`«use»`) da camada de acesso a dados.
- **`diagrama_casos_de_uso.svg`** — os dois atores (Usuário e Administrador)
  e todos os casos de uso do sistema, incluindo relações `«include»` e a
  generalização entre os atores.

---

## 👥 Papéis sugeridos da equipe (conforme o manual do projeto)

| Papel | Sugestão de uso deste projeto |
|---|---|
| Product Owner | Usa este sistema como base para validar com o Grupo B (cliente) quais categorias/setores reais devem ser cadastrados, e ajusta os textos conforme o retorno |
| Scrum Master | Usa o `schema.sql` como ponto de partida do banco e organiza o Kanban em cima dos módulos já prontos (Categorias, Setores, Recursos, Reservas) |
| Dev Team | Pode expandir funcionalidades (ex: paginação, edição de perfil, relatórios) usando as classes já existentes como padrão |

---

## 🛠️ Próximos passos sugeridos (não incluídos neste pacote)

- Aplicativo Android (Fase 2 do backlog), consumindo `api/api_recursos.php`
- Página de "esqueci minha senha"
- Paginação no catálogo para grandes volumes de recursos
