# Especificação do Jogo: Mayab (Baseado no Jogo 07 - Escolha Seu Destino)

## Banco de Dados (`mayab`)
Além de `usuarios` (id, nome, apelido, email, senha, perfil)[cite: 4, 25] e `partidas`[cite: 4]:
- `cenas`: `id`, `titulo`, `texto`, `imagem`, `tipo` (`ENUM('normal', 'final_bom', 'final_ruim')`)[cite: 14].
- `escolhas`: `id`, `cena_id` (origem), `texto`, `destino_id` (cena de destino), `efeito_vida` (INT), `efeito_moedas` (INT)[cite: 14].
- `partidas`: `id`, `usuario_id`, `cena_final_id`, `vida_final`, `moedas_final`, `pontos`, `jogada_em`[cite: 4, 14, 25].

## Regras de Negócio da Partida (`jogo.php`)
1. **Início:** Começa obrigatoriamente na Cena 1 (`id = 1`), com `vida = 10` e `moedas = 0`[cite: 14].
2. **Validação de Escolha (Anti-trapaça):** Ao receber uma escolha do jogador, validar no banco com `SELECT * FROM escolhas WHERE id = ? AND cena_id = ?` usando o `cena_id` atual salvo na sessão[cite: 14]. Escolhas que não pertencem à cena atual devem ser ignoradas[cite: 14].
3. **Atualização de Estado:** Somar/subtrair `efeito_vida` e `efeito_moedas` e atualizar a cena atual para `destino_id`[cite: 14].
4. **Condição de Fim:** A partida termina imediatamente se `vida <= 0` OU se a cena de destino tiver `tipo` diferente de `'normal'` (`final_bom` ou `final_ruim`)[cite: 14].
5. **Cálculo de Pontos (no PHP, gravado uma única vez ao terminar)[cite: 1, 4]:**
   - Final bom (`tipo === 'final_bom'` e `vida > 0`): `500 + (moedas * 10) + (vida * 20)`[cite: 14].
   - Final ruim ou vida zerada (`vida <= 0`): `moedas * 5` (garantir mínimo 0 se moedas ficar negativo)[cite: 14].