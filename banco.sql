-- Banco de Dados para o jogo Mayab
-- Codificação: utf8mb4

CREATE DATABASE IF NOT EXISTS `mayab`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `mayab`;

-- --------------------------------------------------------
-- Estrutura da tabela `usuarios`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `apelido` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `perfil` ENUM('admin', 'jogador') NOT NULL DEFAULT 'jogador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `cenas`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cenas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `texto` TEXT NOT NULL,
  `imagem` VARCHAR(255) NULL,
  `tipo` ENUM('normal', 'final_bom', 'final_ruim') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `escolhas`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `escolhas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cena_id` INT NOT NULL,
  `texto` VARCHAR(255) NOT NULL,
  `destino_id` INT NOT NULL,
  `efeito_vida` INT NOT NULL DEFAULT 0,
  `efeito_moedas` INT NOT NULL DEFAULT 0,
  CONSTRAINT `fk_escolhas_cena` FOREIGN KEY (`cena_id`) REFERENCES `cenas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_escolhas_destino` FOREIGN KEY (`destino_id`) REFERENCES `cenas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `partidas`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `partidas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `cena_final_id` INT NOT NULL,
  `vida_final` INT NOT NULL,
  `moedas_final` INT NOT NULL,
  `pontos` INT NOT NULL,
  `jogada_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_partidas_ranking` (`pontos`, `vida_final`, `moedas_final`, `jogada_em`),
  INDEX `idx_partidas_usuario_data` (`usuario_id`, `jogada_em`, `id`),
  CONSTRAINT `fk_partidas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_partidas_cena` FOREIGN KEY (`cena_final_id`) REFERENCES `cenas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Inserção de usuários iniciais (Admin e Jogador)
-- admin@jogo.com / admin123
-- jogador@jogo.com / jogador123
-- --------------------------------------------------------
INSERT INTO `usuarios` (`id`, `nome`, `apelido`, `email`, `senha`, `perfil`) VALUES
(1, 'Administrador Mayab', 'admin', 'admin@jogo.com', '$2y$10$aOQgvxA2axUOtcm/5NE.yOrdgFRAeRQwjmxDkLEVfKOrNzXua5Qjm', 'admin'),
(2, 'Jogador Explorador', 'jogador', 'jogador@jogo.com', '$2y$10$BRnKXFjbGpFUk42LZ2L88OtdjnD4c3Ilv0wMJ90CPL/HIsaOKE2xO', 'jogador')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

SET NAMES utf8mb4;
USE `mayab`;

-- --------------------------------------------------------
-- Atualiza as 12 cenas iniciais do jogo
-- --------------------------------------------------------
INSERT INTO `cenas` (`id`, `titulo`, `texto`, `imagem`, `tipo`) VALUES
(1, 'O Despertar na Selva de Mayab', 'Você desperta entre ruínas maias envoltas por névoa e cipós. O ar é denso com o aroma de orquídeas e terra molhada. À sua frente ergue-se uma pirâmide milenar, enquanto uma trilha estreita avança pela mata e estelas entalhadas sussurram segredos esquecidos.', NULL, 'normal'),
(2, 'A Pirâmide Esquecida', 'Você sobe os degraus íngremes de calcário e alcança o topo da pirâmide. O vento sopra forte sobre o altar sacrificial. Duas aberturas revelam caminhos: um túnel escuro descendo para o coração da estrutura e uma rota que descende para o vale verdejante.', 'assets/img/MAYAB_Cenas_02_a_12_4K/02_A_Piramide_Esquecida.jpg', 'normal'),
(3, 'A Trilha dos Jaguares', 'A vegetação fecha-se ao seu redor. Galhos estalam nas sombras e olhos amarelos brilham na penumbra. Os jaguares sagrados de Mayab observam cada movimento seu com atenção predatória.', 'assets/img/MAYAB_Cenas_02_a_12_4K/03_A_Trilha_dos_Jaguares.jpg', 'normal'),
(4, 'O Santuário dos Glifos', 'Paredes esculpidas com relevos detalhados relatam as profecias do calendário solar. No centro da sala, há um pedestal com inscrições astronômicas e uma passagem subterrânea lacrada por pedras polidas.', 'assets/img/MAYAB_Cenas_02_a_12_4K/04_O_Santuario_dos_Glifos.jpg', 'normal'),
(5, 'As Catacumbas Subterrâneas', 'Corredores labirínticos de pedra calcária se estendem sob a terra. Tochas ancestrais emitem uma luz bruxuleante azulada. O chão esconde lajes de pressão que acionam armadilhas antigas.', 'assets/img/MAYAB_Cenas_02_a_12_4K/05_As_Catacumbas_Subterraneas.jpg', 'normal'),
(6, 'As Margens do Cenote Sagrado', 'Diante de você surge uma cratera natural com águas límpidas em tom turquesa. No fundo, vislumbram-se reflexos dourados de antigas oferendas aos deuses da chuva, mas a descida é traiçoeira.', 'assets/img/MAYAB_Cenas_02_a_12_4K/06_As_Margens_do_Cenote_Sagrado.jpg', 'normal'),
(7, 'O Observatório Astral de Caracol', 'Uma imponente torre circular dedicada a Chaac e às estrelas. Fendas na cúpula projetam feixes solares sobre um mapa celeste esculpido em jade, revelando rotas sagradas.', 'assets/img/MAYAB_Cenas_02_a_12_4K/07_O_Observatorio_Astral_de_Caracol.jpg', 'normal'),
(8, 'O Pátio do Jogo de Pelota Sagrado', 'Um amplo campo cerimonial cercado por muralhas monumentais com aros de pedra no alto. A energia dos antigos rituais ainda vibra no ar, guardando os portais que levam ao destino supremo.', 'assets/img/MAYAB_Cenas_02_a_12_4K/08_O_Patio_do_Jogo_de_Pelota_Sagrado.jpg', 'normal'),
(9, 'A Câmara das Sombras e Sacrifícios', 'Um salão soturno dominado por estátuas de guerreiros com máscaras de obsidiana. O teto range sob o peso das ruínas, e a tensão entre a luz da salvação e o abismo do submundo é quase palpável.', 'assets/img/MAYAB_Cenas_02_a_12_4K/09_A_Camara_das_Sombras_e_Sacrificios.jpg', 'normal'),
(10, 'O Tesouro Supremo de Kukulkan', 'Você atravessa os grandes portais dourados e contempla o magnífico relicário de Kukulkan! Montanhas de ouro, estátuas de jade e a lendária máscara solar estão ao seu alcance. Os sacerdotes do templo reconhecem sua bravura e você retorna ao mundo como uma lenda viva!', 'assets/img/MAYAB_Cenas_02_a_12_4K/10_O_Tesouro_Supremo_de_Kukulkan.jpg', 'final_bom'),
(11, 'A Sabedoria dos Deuses das Estrelas', 'As portas secretas do observatório revelam uma biblioteca com códices ancestrais intactos contendo o conhecimento astronômico e espiritual de Mayab. Você desvenda os mistérios do universo e traz sabedoria eterna para a humanidade!', 'assets/img/MAYAB_Cenas_02_a_12_4K/11_A_Sabedoria_dos_Deuses_das_Estrelas.jpg', 'final_bom'),
(12, 'O Aprisionamento no Inframundo de Xibalba', 'O solo cede com um estrondo assustador e você é precipitado nas profundezas gélidas de Xibalba. Os Senhores do Medo e da Escuridão selam seu destino para sempre nas cavernas esquecidas. Sua jornada termina em ruína.', 'assets/img/MAYAB_Cenas_02_a_12_4K/12_O_Aprisionamento_no_Inframundo_de_Xibalba.jpg', 'final_ruim')
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`), `texto` = VALUES(`texto`), `imagem` = VALUES(`imagem`), `tipo` = VALUES(`tipo`);

-- --------------------------------------------------------
-- Atualiza as 24 escolhas do jogo
-- --------------------------------------------------------
INSERT INTO `escolhas` (`id`, `cena_id`, `texto`, `destino_id`, `efeito_vida`, `efeito_moedas`) VALUES
(1, 1, 'Subir as escadarias íngremes da pirâmide ancestral', 2, -1, 5),
(2, 1, 'Seguir cautelosamente pela trilha fechada da selva', 3, 0, 2),
(3, 1, 'Examinar os glifos e estelas de pedra ao lado da entrada', 4, 1, 3),
(4, 2, 'Adentrar o túnel escuro rumo às entranhas da pirâmide', 5, -2, 10),
(5, 2, 'Decifrar os relevos do altar solar em busca de orientação', 4, 2, 4),
(6, 2, 'Descer pelo lado oposto em direção ao reflexo de um cenote', 6, -1, 0),
(7, 3, 'Correr velozmente em direção à clareira com água cristalina', 6, -2, 1),
(8, 3, 'Subir em uma grande árvore sumaúma e analisar as ruínas', 4, -1, 5),
(9, 3, 'Acender uma tocha com folhas secas para afugentar as feras', 7, 0, -2),
(10, 4, 'Consultar o mapa astronômico no pedestal de pedra', 7, 1, 8),
(11, 4, 'Descer pela passagem subterrânea lacrada', 5, -1, 3),
(12, 5, 'Pisar com cautela apenas nas lajes com o símbolo sagrado', 8, 0, 6),
(13, 5, 'Correr pelo corredor tentando desviar das flechas antigas', 9, -4, 10),
(14, 6, 'Mergulhar nas águas profundas para resgatar oferendas de ouro', 9, -2, 15),
(15, 6, 'Seguir pelo canal de pedras que margeia o cenote até o campo cerimonial', 8, 1, 2),
(16, 6, 'Realizar uma oferenda respeitosa jogando moedas para os deuses da água', 7, 3, -5),
(17, 7, 'Alinhar as lentes de cristal celestial com o sol nascente', 8, 2, 5),
(18, 7, 'Pegar o amuleto de jade estelar e seguir para o salão interno', 9, 0, 8),
(19, 8, 'Arremessar a bola de látex ritual pelo aro sagrado de pedra', 10, 2, 20),
(20, 8, 'Ajoelhar-se e estudar os ensinamentos dos mestres astrônomos', 11, 3, 15),
(21, 8, 'Arrombar as grades de madeira apodrecida abaixo do campo', 12, -5, 0),
(22, 9, 'Seguir a tênue luz de tochas que indica a câmara de sabedoria', 11, -1, 10),
(23, 9, 'Lançar-se através do portal dourado que resplandece ao longe', 10, -3, 25),
(24, 9, 'Tocar o sarcófago proibido de obsidiana no centro da sala', 12, -8, 0)
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`), `destino_id` = VALUES(`destino_id`), `efeito_vida` = VALUES(`efeito_vida`), `efeito_moedas` = VALUES(`efeito_moedas`);