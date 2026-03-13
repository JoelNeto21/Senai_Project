# 📚 SenaiStock - API de Controle de Estoque

**Autores:** Joel, Júlio, Murilo | **Local:** SENAI Limeira (2026)

https://app.milanote.com/1VTtUj1yQuuz4N?p=ZK5fK3a4HXt

<br>

---

## Sobre o Projeto

O **SenaiStock** é uma API Back-End desenvolvida para resolver um problema crítico de controle quantitativo de livros didáticos. 

O Senai envia periodicamente grandes remessas de livros para as unidades de ensino, que ficam armazenados no almoxarifado e são retirados pelos instrutores conforme a necessidade. O problema atual é a falta de controle sobre o saldo remanescente, gerando atrasos no aprendizado quando o estoque zera inesperadamente.

**Objetivo:** Manter o saldo do estoque sempre atualizado, permitindo somar livros quando chegam da editora e subtrair (dar baixa) quando são retirados para as turmas.

<br>

---

## Funcionalidades Essenciais

* **🔒 Autenticação Simples:** Sistema de login para garantir que apenas funcionários autorizados (Almoxarife ou Coordenador) possam alterar o estoque.
* **📚 Catálogo de Livros:** Cadastro dos títulos disponíveis contendo Título, ISBN e Matéria.
* **📦 Entrada de Estoque (Abastecimento):** Rota para registrar a chegada de caixas, somando a quantidade informada ao saldo atual.
* **📤 Saída de Estoque (Baixa Manual):** Rota para registrar a retirada de livros informando a quantidade e o motivo. Bloqueia a operação se o estoque for insuficiente.
* **⚠️ Monitoramento de Saldo:** Rota que lista os livros com estoque abaixo de um nível mínimo (menos de 10 unidades), indicando necessidade de reposição.

<br>

---

## Levantamento de Requisitos

### Requisitos Funcionais (RF)
O que o sistema faz diretamente para o usuário.

| ID | Requisito | Descrição |
| :--- | :--- | :--- |
| **RF01** | Autenticação de Usuários | O sistema deve permitir o login de funcionários autorizados (Almoxarife ou Coordenador). |
| **RF02** | Cadastro de Livros | Deve ser possível cadastrar títulos com Título, ISBN e Matéria. |
| **RF03** | Entrada de Estoque | O sistema deve permitir registrar a chegada de novas remessas, informando o livro e a quantidade para somar ao saldo. |
| **RF04** | Saída de Estoque (Baixa) | Deve permitir registrar a retirada de livros para as turmas, informando livro, quantidade e motivo (ex: Turma A). |
| **RF05** | Validação de Saldo | O sistema deve bloquear a saída de livros se a quantidade solicitada for maior que o saldo disponível. |
| **RF06** | Monitoramento de Nível Mínimo| Deve listar automaticamente livros com estoque abaixo de 10 unidades para reposição. |
| **RF07** | Cálculo de Saldo Atualizado| O sistema deve realizar a operação matemática de somar/subtrair para manter o saldo sempre em tempo real. |
| **RF08** | Listagem de Catálogo | O sistema deve permitir visualizar todos os livros cadastrados e suas quantidades atuais. |

<br>

### Requisitos Não-Funcionais (RNF)
Atributos de qualidade e restrições técnicas do sistema.

| ID | Requisito | Descrição |
| :--- | :--- | :--- |
| **RNF01**| Tecnologia Back-End | O sistema deve ser desenvolvido obrigatoriamente em Laravel (PHP). |
| **RNF02**| Banco de Dados | Deve utilizar o banco de dados relacional MySQL. |
| **RNF03**| Persistência de Dados | O uso do Eloquent ORM é obrigatório para modelar tabelas e relações. |
| **RNF04**| Arquitetura | O sistema deve seguir o padrão de API RESTful, retornando respostas obrigatoriamente em formato JSON. |
| **RNF05**| Qualidade de Código | O código deve seguir os padrões PSR e os princípios de Clean Code. |
| **RNF06**| Segurança | A API deve ser protegida por autenticação para garantir que apenas perfis autorizados alterem o estoque. |
| **RNF07**| Versionamento | O projeto deve ser publicado obrigatoriamente no GitHub. |

<br>

### Requisitos de Negócio (RN)
Políticas que regem o funcionamento do processo de negócio.

| ID | Requisito | Descrição |
| :--- | :--- | :--- |
| **RN01** | Estoque Insuficiente | Nenhuma saída de estoque pode ser processada se o resultado final do saldo for negativo. |
| **RN02** | Nível Crítico | O sistema deve considerar como "estoque baixo" qualquer título com menos de 10 unidades. |
| **RN03** | Perfis de Acesso | Apenas usuários autenticados como "Almoxarife" ou "Coordenador" podem realizar movimentações de entrada e saída. |

<br>

---

## Próximos Passos e Artefatos do Projeto

* **Diagramas:** *(Adicionar link para a pasta de diagramas ou imagens aqui futuramente)*.
* **Prototipagem:** *(Adicionar link para a pasta de protótipos ou imagens aqui futuramente)*.
* **Metodologias Ágeis:** O time utilizará práticas ágeis para organização e distribuição de tarefas durante as Sprints e se organizando pelo modelo Kanban.

<br>

---

**Clone o repositório:**
   ```bash
   git clone [https://github.com/JoelNeto21/Senai_Project.git](https://github.com/JoelNeto21/Senai_Project.git)
   cd Senai_Project
