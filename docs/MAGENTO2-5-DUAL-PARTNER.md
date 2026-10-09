# Magento 2 — dual PagBank / Vindi (5.0+)

Versão **5.0.0+**. Espelho do playbook Woo `WOO-5-DUAL-PARTNER.md` e OpenMage `OPENMAGE-5-DUAL-PARTNER.md`.

## Detecção

| Connect Key | Partner | Host |
|-------------|---------|------|
| `CON…` / `CONSANDBOX…` | PagBank | `ws…/pspro/v7/connect/ws/` (**40** chars) |
| `CONVD…` / `CONVDSANDBOX…` | Vindi | `api…/v1/` — **40** chars prod + sandbox (legado mais curto OK) |

Nunca chame a API Vindi nativa com a Connect Key. Tokenização no browser: `GET /tokenize/config` → `payment_profiles` (chave pública).

**Modelo C:** keys `CONVD…` enviam o contrato canônico PB (centavos, `CREDIT_CARD` / `CREDIT_CARD_3DS`, `gateway_token`). Keys `CON…` continuam no v7.

## Matriz de recursos

| Recurso | PagBank | Vindi |
|---------|---------|-------|
| PIX / boleto | ✓ | ✓ (`api/v1`) |
| Cartão | `encryptCard` | public `payment_profiles` → `gateway_token` |
| 3DS | SDK PagBank | setup → DDC → enroll → challenge → validate (`pagbank/ajax/vindi*`) |
| Tipo no pedido (3DS) | `CREDIT_CARD` + `authentication_method` | `CREDIT_CARD_3DS` + `three_ds` |
| Cartão salvo (Vault) | `card.id` PagBank | `payment_profile_id` quando o token é numérico |
| Soft descriptor | ✓ | omitido |
| Debug upstream | — | `POST orders?debug=1` automático |

## Meta do pagamento (`additional_information`)

Novos pedidos gravam `partner` (`pagbank` \| `vindi`) e `connect_key_fp` (últimos 4 da key).

Vindi CC também grava `payment_profile_id`, `payment_company_code` e `cc_3ds_payload`.

## Classes

- `Model/Partner/Detector.php`
- `Model/Partner/Capabilities.php` — `has3ds` consulta `payment_methods`
- `Model/Partner/Cutoff.php` — **5/nov/2026** (CTA apenas)
- `Model/Partner/Branding.php`
- `Model/Vindi/Proxy.php` — proxies 3DS

## Corte

Notice no admin quando a key é PagBank. Sem hard-kill no módulo. URL: https://pbintegracoes.com/migrar

Tokens do Vault PagBank não migram. Depois da troca para `CONVD…`, o cliente precisa informar o cartão de novo.

## QA rápido

- [ ] CC PagBank (encrypt + 3DS) regressão
- [ ] CC Vindi sem 3DS (`gateway_token`)
- [ ] CC Vindi com 3DS (`CREDIT_CARD_3DS`)
- [ ] PIX e boleto + webhook
- [ ] Parcelas `fees/calculate` com `CONVD`
- [ ] Troca CON→CONVD: novos pedidos na v1; info de pedidos antigos no admin
- [ ] Notice de cutoff só com key PagBank
