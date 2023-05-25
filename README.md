# Exactspotter-Integration-AGI

Este repositório é dedicado a uma customização desenvolvida em um PABX digital que utiliza enginnes do Asterisk para enviar chamadas via API do CRM Exactspotter. 

### Adição de campo na tabela CDR

O PABX utilizado para desenvolver este projeto foi o Issabel com Asterisk 11. Este sistema possui um módulo chamado **CDR** que salva todos os registros de uma chamada de entrada ou de saída dentro um tabela chamada ***CDR*** no banco de dados ***ASTERISKCDRDR***. Antes de iniciar todo o projeto, recomenda-se fortemente criar o campo `dst_custom` dentro da tabela citada anteriormente, coonforme é feito abaixo.

Acessar o banco de dados `asteriskcdrdb` e na aba consulta rodar o seguite código:

```sql
USE asteriskcdrdb.cdr;

ALTER TABLE cdr
ADD COLUMN dst_custom VARCHAR(50) 
DEFAULT NULL;
```
O comando acima irá adicionar a nova coluna na tabela, usada para registrar os números de destino dos clientes.


### Criação do Banco de Dados exactspotter

A fim de registrar as informações das chamadas feitas através do ***ExactSpotter*** criei um banco de dados independente com apenas duas tabelas. Basta abrir a janela de consulta do MySql e executar o script localizado em `/var/lib/asterisk/agi-bin/easytec/exactspotter/important_files/exactspotter.sql`. Após atualizar, o novo bando de dados `exactspotter` será criado, encerrando todas as alterações necessáriasa nível de banco no PABX.

___

### Iniciando o projeto

Para iniciar este projeto basta descarregar todo este repositório no diretório `/var/lib/asterisk/agi-bin/easytec/` e o arquivo `/exactspotter/important_files/exactspotter.sql` deve ser colocado dentro do diretório `/etc/asterisk/easytec/`. Após upload desses arquivos para o servidor, é necessário ajustar as credenciais de acesso ao banco de dados e da API nos arquivos `entra_chamada`, `encerra_chamada`, `config.ini` e ajustar também o nome do tronco de ligações no contexto personalizado `exactspoter_ativo`, conforme mostra no exemplo abaixo:

```conf
same => n,Dial(SIP/31950002/${destination},300,Tt)
```

**Atençaõ** - Não podemos esquecer de dar permissão ***chmod -R 77*** para todos estes arquivos.

### Editando extensions_custom.conf

Para o PABX identificar o nosso contexto customizado em sua pilha de arquivos, devemos editar uma linha do arquivo `/etc/asterisk/extensions_custom.conf` a fim de **incluir** o contexto, ficando da seguinte forma:

```conf
; This file contains example extensions_custom.conf entries.
; extensions_custom.conf should be used to include customizations
; to AMP's Asterisk dialplan.

; Extensions in AMP have access to the 'from-internal' context.
; The context 'from-internal-custom' is included in 'from-internal' by default

[from-internal-custom]
exten => 1234,1,Playback(demo-congrats)         ; extensions can dial 1234
exten => 1234,2,Hangup()
exten => h,1,Hangup()

#include easytec/exactspotter_ativo.conf //Esta linha inclui o contexto
```
___

### Finalizando os ajustes do projeto

O restantes dos ajustes devem ser feitos dentra da interface do Issabel, sendo:

**1°** - Criação ***Custom Destinations*** que faça a chamada do contexto citado acima.

**2°** - Criação de uma ***Outbound Routes*** enviando para o Custom Destinations criado.

**3°** - Criação de uma ***Class of Service*** que permita apenas extensões, filas e a rota criada acima.

**4°** - Adicionar todos os ramais que irão utilizar ExactSpotter na Class Of Service criada.