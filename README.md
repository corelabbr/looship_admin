# MeuPrime_Admin

> Módulo de clube de frete para Magento


## Testado em Magento

`1.6.2.0`, `1.7.0.2`, `1.8.1.0` e `1.9.2.1`

## Instalando manualmente

Caso você prefira fazer a instalação manual, basta baixar a última versão do módulo na [página de 
releases](https://github.com/corelabbr/looship_admin/releases) e seguir os seguintes passos:

- O tarball deve ser descompactado no public_html de sua loja
- <a href="#cache">Atualize o cache</a>
- Se você utiliza Flat Table, <a href="#flattable">atualize sua Flat Table</a>

## Instalando com [modman](https://github.com/colinmollenhour/modman)

    $ cd /path/to/magento
    $ modman init
    $ modman clone https://github.com/corelabbr/looship_admin

## Instalando com [modgit](https://github.com/jreinke/modgit)

    $ cd /path/to/magento
    $ modgit init
    $ modgit add looship-admin https://github.com/corelabbr/looship_admin


## Configurando o módulo

Antes de configurar o módulo você deve cadastrar o CEP de origem de sua loja:

- Acesse a administração de sua loja
- No menu superior vá em "Sistema > Configuração"
- No menu esquerdo vá em "Definições de Envio"
- Na aba "Origem" você pode preencher os dados da origem de entrega de sua loja
- Se você tem a opção de compilação habilitada precisa recompilar em "Sistemas > Ferramentas > Compilação"

Para acessar a configuração do módulo:

- Acesse a administração de sua loja
- No menu superior vá em "Sistema > Configuração"
- No menu esquerdo vá em "Métodos de Envio"

Na aba "Meu Prime" você tem todos os campos de configuração do módulo, os mais importantes são:

- **Habilitar** - Para "ligar" ou "desligar" o módulo
- **Token** - Token de registro do lojista na plataforma Meu Prime.

## Suporte

Por favor utilize as [issues do GitHub](https://github.com/corelabbr/looship_admin/issues) para reportar problemas 
e requisitar features. Por favor verifique as issues já criadas e envie sua pull request!

Para entrar em contato com o criador, vá para [https://developers.looship.com.br/](https://developers.looship.com.br/).


## FAQ

<a name="cache"></a>
### Como atualizar cache?

O cache é uma funcionalidade do Magento para aumentar a velocidade de sua loja, porém, em alguns casos, é 
necessário atualizá-lo para aplicar modificações na loja:

- Acesse a administração de sua loja
- No menu superior vá em "Sistema > Cache Management"
- No lado esquerdo, no cabeçalho da tabela, clique no link "Selecionar Tudo"
- No lado direito, no cabeçalho da tabela, selecione o campo "Ações" como "Atualizar" e clique no botão "Enviar"

Você também pode apagar todo o conteúdo da pasta "var/cache" para atualizar seu cache.

<a name="flattable"></a>
### Como atualizar o flat table?

Flat Table é uma funcionalidade do Magento que agrupa todos os atributos de produtos em uma tabela só, por padrão 
ela vem desativada, mas você ou seu desenvolvedor pode ativá-la para aumentar o desempenho da loja.

O módulo looship-admin inclui os campos de taxonomia no cadastro do produto, e quando você utiliza a Flat 
Table é necessário atualizá-la para aplicar esses campos:

- Acesse a administração de sua loja
- No menu superior vá em "Sistema > Index Management"
- No lado esquerdo, no cabeçalho da tabela, clique no link "Selecionar Tudo"
- No lado direito, no cabeçalho da tabela, selecione o campo "Ações" como "Reindex Data" e clique no botão "Enviar"

<a name="log"></a>
### Como habilitar o log?

O log permite que os erros gerados pelo módulo sejam rastreados para podermos entender melhor o que está 
acontecendo sem atrapalhar os usuários da loja.

Para habilitar essa funcionalidade:

- Acesse a administração de sua loja;
- No menu superior vá em "Sistema > Configuração"
- No menu esquerdo vá em "Desenvolvedor", a última opção do menu
- Na aba "Log Settings", selecione "Habilitado" como "Sim"
- Clique em "Salvar Config"

A partir de agora sua loja salvará os erros no arquivo `var/log/system.log`.

## Continuous integration

Antes de fazer o commit de qualquer código, execute o lint e code sniffer.

```bash
find ./app -name "*.php" -exec php -l {} \;
./bin/phpcs --extensions=php --standard=./ruleset.xml ./app
```

*O Magento não segue nenhum code style, por isso compilei uma lista de checks no `ruleset.xml`.*

## Licença

[MIT](https://github.com/corelabbr/looship_admin/blob/master/LICENSE) © [Meu Prime](https://developers.looship.com.br).
