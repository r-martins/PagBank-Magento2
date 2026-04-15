[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg?style=flat-square)](https://php.net/)
[![GitHub release](https://img.shields.io/github/v/release/r-martins/PagBank-Magento2)](https://github.com/r-martins/PagBank-Magento2)
![GitHub last commit (branch)](https://img.shields.io/github/last-commit/r-martins/PagBank-Magento2/master)
# Módulo PagBank(PagSeguro) para Magento2

## Nova Geração - Novas APIs, Novos Recursos, Mais Estabilidade
Aceite mais de 30 cartões de crédito, PIX e boleto em sua loja Magento 2, usando o meio de pagamento mais aceito pelos brasileiros.

Agora usando as Novas APIs do PagBank.

# Recursos

- Aceite pagamentos com Cartão de Crédito, PIX ou Boleto de forma transparente (sem sair da loja)
- [3D Secure](https://ajuda.pbintegracoes.com/hc/pt-br/articles/22375922278157-Autentica%C3%A7%C3%A3o-3DS-Sua-prote%C3%A7%C3%A3o-contra-Chargeback) em Pagamentos com Cartão - Zero Chargeback fraudulento, MUITO mais conversão, sem necessidade de antifraude.
- Permite [salvar o cartão](https://ajuda.pbintegracoes.com/hc/pt-br/articles/22592192276109-Salvar-Cart%C3%A3o-para-uso-posterior) para compras futuras (Magento Vault)
- PIX e Boleto com [validade configurável](https://ajuda.pbintegracoes.com/hc/pt-br/articles/27060110031373-PIX-e-Boleto-com-validade-configur%C3%A1vel)
- Atualização automática do status do pedido
- Pagamento em 1x ou [parcelado](https://ajuda.pbintegracoes.com/hc/pt-br/articles/22592267621645-Op%C3%A7%C3%B5es-de-Parcelamento-no-Magento-2)
- Identificador do nome da loja na fatura
- Descontos nas taxa oficiais do PagBank (ou suas taxas)
- Suporte a Sandbox
- Link direto para a transação disponível no admin
- Suporte a todos os tipos de produtos
- Suporte a multi-loja
- Desenvolvido nos padrões Magento 2 por desenvolvedores certificados pela Adobe 🏆
- [Atualização pró-ativa de pedidos](https://ajuda.pbintegracoes.com/hc/pt-br/articles/27270191360653-For%C3%A7ar-atualiza%C3%A7%C3%A3o-de-pedidos)

<details>
  <summary>VEJA ALGUNS SCREENSHOTS (clique aqui para expandir)</summary>
  <img src="https://github.com/r-martins/PagBank-Magento2/assets/191149/e1f9ab4e-0eed-40d5-b804-71de4bf57f10" alt="Cartão de Crédito no Checkout" title="Cartão de Crédito no Checkout"/>
  <img src="https://github.com/r-martins/PagBank-Magento2/assets/191149/399c0cfd-9547-49f6-8fa9-520b4c786adc" alt="PIX - Tela de Sucesso" title="PIX - Tela de Sucesso"/>
  <img src="https://github.com/r-martins/PagBank-Magento2/assets/191149/8c464d9f-13b9-47e1-ae83-777933c0ab67" alt="Configurações de cartão de crédito" title="Configurações de cartão de crédito"/>
  <img alt="PIX - Configurações" src="https://github.com/r-martins/PagBank-Magento2/assets/191149/22460357-5657-4c4d-bb39-521803a7ecb8" title="PIX - Configurações"/>
  <img alt="Tela do pedido no admin" src="https://github.com/r-martins/PagBank-Magento2/assets/191149/67b70ad3-e41a-40f3-a7b5-95356708a303" title="Tela do pedido no admin"/>
  <img src="https://github.com/r-martins/PagBank-Magento2/assets/191149/9f02ed3d-8bab-4095-8f87-e7492426e277" alt="Autenticação 3D Secure" title="Autenticação 3D Secure"/>
</details>

# Pré-requisitos
- Magento 2.4.4 ou superior
- PHP 8.1 ou superior

# Instalação

- Abra o terminal, navegue até a pasta inicial do Magento e digite:
  - `composer require ricardomartins/pagbank-magento2`
  - `bin/magento setup:upgrade`
  - `bin/magento setup:di:compile`
  - `bin/magento setup:static-content:deploy`
- Navegue até Lojas &gt; Configurações &gt; Vendas &gt; Métodos de Pagamento &gt; Soluções Recomendadas > PagBank (Ricardo Martins PagBank) e clique em Configurar
  - Clique em "Obter Connect Key" e siga as instruções para obter sua Connect Key e preenche-la no campo indicado logo abaixo.
  - Salve as configurações e você está pronto para vender.
- Se desejar, configure opções de parcelamento, e validade do boleto e código pix de acordo com suas necessidades.


# Autores
- Ricardo Martins (@magenteiro) - [Adobe Certified Professional](https://www.credly.com/badges/8a2af83e-60c6-447a-b8e5-9154dd97751b) 🏆
- Ligia Salzano (@ligiasalzano) - [Adobe Certified Professional - 3x](https://www.credly.com/users/ligia-salzano) 🏆🏆🏆

&ast; Estes são os autores da versão inicial. Novos autores e colaboradores não certificados podem vir a contribuir com futuras versões e podem ser encontrados [aqui](https://github.com/r-martins/PagBank-Magento2/graphs/contributors).

# Perguntas Frequentes (FAQ)

## Como funcionam os descontos nas taxas?

Ao usar nossas integrações no modelo de recebimento em 14 ou 30 dias, ao invés de pagar 4,99% ou 3,99%, você pagará cerca de 0,60% a menos e estará isento da taxa de R$0,40 por transação.

Taxas menores são aplicadas para transações parceladas, PIX e Boleto.

Consulte mais sobre elas no nosso site.

## Eu tenho uma taxa ou condição negociada menor que estas. O que faço?

Ao usar nossa integração, nossas taxas e condições serão aplicadas ao invés das suas. Isto é, nas transações realizadas com nosso plugin.

É importante notar que taxas negociadas no mundo físico (moderninhas) não são aplicadas no mundo online.

Se mesmo assim você possuir uma taxa ou condição melhor, e se compromete a faturar mais de R$20 mil / mês (pedidos aprovados usando nossa integração), podemos incluir sua loja em uma aplicação especial. Basta selecionar o modelo "Minhas taxas" quando obter sua Connect Key.


## Tenho outra pergunta não listada aqui

Consulte nossa [Central de ajuda](https://ajuda.pbintegracoes.com/hc/pt-br/) e [entre em contato](https://ajuda.pbintegracoes.com/hc/pt-br/requests/new) conosco se não encontrar sua dúvida respondida por lá.

A maioria das dúvidas estão respondidas lá. As outras são respondidas em até 2 dias após entrar em contato.

## O plugin atualiza os status automaticamente?

Sim.

E quando há uma transação no PagBank, um link para ela é exibida na página do pedido. Assim você pode confirmar novamente o status do mesmo.

## Como posso testar usando a Sandbox?

Basta clicar no botão 'Obter Connect Key para Testes' localizado nas configurações do plugin, seguir as instruções, e informar sua Connect Key de testes no campo indicado.

Um link para mais detalhes sobre como utilizar a Sandbox está disponível na página de configurações do plugin.

A equipe do PagBank está trabalhando numa correção.

Enquanto isso, você pode testar com dados reais e realizar o estorno. As tarifas e taxas são reembolsadas, não incidindo nenhum custo.

## Este é um plugin oficial?

Este plugin foi desenvolvido por [PagBank Integrações](https://pbintegracoes.com/?utm_source=github-m2&utm_content=readme&utm_medium=link), parceiro oficial PagBank desde 2014.

Ou seja, apesar da parceria oficial, suporte e constante manutenção e atualizações, este plugin não foi desenvolvido pelo PagBank.

PagSeguro e PagBank são marcas do UOL.


## Posso modificar e comercializar este plugin?

O plugin é licenciado sob GPL v3. Você pode modificar e distribuir, contanto que suas melhorias e correções sejam contribuidas de volta com o projeto.

Você deve fazer isso através de Pull Requests ao [repositório oficial no github](https://github.com/r-martins/PagBank-Magento2).

# Garantia

Conhecido como "software livre", este plugin é distribuido sem garantias de qualquer tipo.

O desenvolvedor ou PagBank não se responsabilizam por quaisquer danos causados pelo uso (ou mal uso) deste plugin.

Esta é uma iniciativa pessoal, sem vínculo com PagBank. PagBank é uma marca do UOL.

Este não é um produto oficial do PagBank.

Ao usar este plugin você concorda com os [Termos de Uso e Política de Privacidade](https://pbintegracoes.com/terms/?utm_source=github-m2&utm_content=readme&utm_medium=link).

# Links úteis

- [Site Oficial das Integrações PagBank por Ricardo Martins](https://pbintegracoes.com/?utm_source=github-m2&utm_content=readme&utm_medium=link)
- [Central de Ajuda](https://ajuda.pbintegracoes.com.com/hc/pt-br/)
- [Termos de Uso e Política de Privacidade](https://pbintegracoes.com/terms/?utm_source=github-m2&utm_content=readme&utm_medium=link)
- [Obter Connect Key](https://pbintegracoes.com/connect/autorizar/?utm_source=github-m2&utm_content=readme&utm_medium=link)
