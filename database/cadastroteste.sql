-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/03/2026 às 03:48
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cadastroteste`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrinho`
--

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `sessao_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `data_nascimento` date NOT NULL,
  `genero` enum('Masculino','Feminino','Outro') NOT NULL,
  `endereco_faturamento` text NOT NULL,
  `cep_faturamento` varchar(8) NOT NULL,
  `numero_faturamento` varchar(20) NOT NULL,
  `complemento_faturamento` varchar(255) DEFAULT NULL,
  `bairro_faturamento` varchar(255) DEFAULT NULL,
  `cidade_faturamento` varchar(255) DEFAULT NULL,
  `uf_faturamento` char(2) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_entrega`
--

CREATE TABLE `enderecos_entrega` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `uf` char(2) NOT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `endereco_padrao` enum('Sim','Não') NOT NULL DEFAULT 'Não'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens_produto`
--

CREATE TABLE `imagens_produto` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `caminho_imagem` varchar(255) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `imagens_produto`
--

INSERT INTO `imagens_produto` (`id`, `produto_id`, `caminho_imagem`, `principal`) VALUES
(18, 1, 'img/Heineken600 01.png', 1),
(19, 1, 'img/Heineken600 02.png', 0),
(24, 4, 'img/Serramalte 01.png', 1),
(25, 4, 'img/Serramalte 02.png', 0),
(26, 5, 'img/BrahmaChopp 01.png', 1),
(27, 5, 'img/BrahmaChopp 02.png', 0),
(28, 6, 'img/AguaComGas 01.png', 1),
(29, 6, 'img/AguaComGas 02.png', 0),
(30, 7, 'img/XaropeAmora.png', 1),
(31, 7, 'img/XaropeBlueCuracao.png', 0),
(32, 7, 'img/XaropeManga.png', 0),
(33, 7, 'img/XaropeMorango.png', 0),
(34, 7, 'img/XaropeTangerina.png', 0),
(37, 9, 'img/Campari 01.png', 1),
(38, 9, 'img/Campari 02.png', 0),
(39, 10, 'img/Saque 01.png', 1),
(40, 10, 'img/Saque 02.png', 0),
(41, 11, 'img/Absolut 01.png', 1),
(42, 11, 'img/Absolut 02.png', 0),
(43, 12, 'img/Smirnoff 01.png', 1),
(44, 12, 'img/Smirnoff 02.png', 0),
(45, 13, 'img/Ballantines 01.png', 1),
(46, 13, 'img/Ballantines 02.png', 0),
(51, 16, 'img/Tanqueray 01.png', 1),
(52, 16, 'img/Tanqueray 02.png', 0),
(55, 18, 'img/GarrafaVinho 01.png', 1),
(56, 18, 'img/GarrafaVinho 02.png', 0),
(60, 20, 'img/690fe841f1264-67e72a61341fe-f5f7bd9cca3d9084174a4a4e141b33cc.jfif', 1),
(61, 20, 'img/690fe841f2083-67e72a61782c6-24-beea4f66ddf0cc9f0017056724502114-1024-1024.jpg', 0),
(63, 19, 'img/690fe94fd16a0-Saque 01.png', 1),
(64, 19, 'img/690fe94fd2838-Saque 02.png', 0),
(65, 17, 'img/690fe9e4263aa-TaçaDeVinho 01.png', 1),
(66, 17, 'img/690fe9e427b38-TaçaDeVinho 02.png', 0),
(67, 15, 'img/690fea9d0b337-SãoFrancisco 01.png', 1),
(68, 15, 'img/690fea9d0cab0-SãoFrancisco 02.png', 0),
(69, 14, 'img/690feaef84a60-CachaçaMineira 01.png', 1),
(70, 14, 'img/690feaef8640d-CachaçaMineira 02.png', 0),
(73, 3, 'img/690fefcff2653-67d8d7be71260-cachaca-seleta.png', 1),
(74, 3, 'img/690fefcff32ee-67d8d7be71607-61b0f2c5-5c38-43f0-b40a-7262ac1e09ff.jpg', 0),
(75, 2, 'img/690ff02695eed-Heineken600 01.png', 1),
(76, 2, 'img/690ff02696cd5-Heineken600 02.png', 0),
(77, 21, 'img/690ff152ae749-67d8d3b6d2b19-ee771359-c5ab-4201-823e-caa5dc527359.jfif', 1),
(78, 21, 'img/690ff152aea8d-67d8d3b6d224e-a29fa5e9-0281-47d0-97a7-ede1bd506024.jfif', 0),
(79, 22, 'img/ice1.jfif', 0),
(80, 22, 'img/ice.jfif', 1),
(82, 23, 'img/690ff22b5a9ae-67d8bdfb7b122-56387393-c82c-460e-998a-7fc90c7015e2.jfif', 1),
(83, 23, 'img/690ff22b5b96a-67d8bdfb8cc2c-c5b3ef6a-ea01-4e15-b3de-0373d6242eef.jfif', 0),
(84, 8, 'img/690ff2a979c5e-Original 02.png', 0),
(85, 8, 'img/690ff2a97a88d-Original600.png', 1),
(86, 24, 'img/690ff32711a6f-AguaDeCoco.png', 1),
(87, 24, 'img/690ff32711efd-AguaDeCocoMaracuja.png', 0),
(88, 24, 'img/690ff327121dc-AguaDeCocoMelancia.png', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_pedido`
--

INSERT INTO `itens_pedido` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco`, `subtotal`) VALUES
(16, 53, 0, 0, 0.00, 0.00),
(17, 54, 19, 2, 39.00, 78.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `endereco_id` int(11) DEFAULT NULL,
  `forma_pagamento` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `valor_frete` decimal(10,2) NOT NULL,
  `total_com_frete` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'aguardando pagamento',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(200) DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(2000) DEFAULT NULL,
  `avaliacao` decimal(2,1) DEFAULT 0.0,
  `status` enum('Ativo','Desativado') NOT NULL DEFAULT 'Ativo',
  `peso` decimal(10,2) DEFAULT NULL,
  `altura` decimal(10,2) DEFAULT NULL,
  `largura` decimal(10,2) DEFAULT NULL,
  `comprimento` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `codigo`, `nome`, `quantidade`, `valor`, `descricao`, `avaliacao`, `status`, `peso`, `altura`, `largura`, `comprimento`) VALUES
(1, 'P001', 'Heineken Long Neck', 13, 11.90, 'Heineken Premium Lager (Long Neck) A icônica lager premium holandesa em sua versão individual. Celebrada mundialmente por seu sabor puro malte, refrescância distinta e notas frutadas sutis. Uma experiência perfeitamente equilibrada e prática, que entrega o sabor clássico da Heineken com sofisticação e conveniência.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(2, 'P002', 'Heineken 600ml', 23, 16.50, 'Heineken Premium Lager (600ml) O ícone global da cerveja premium, reconhecido instantaneamente por sua garrafa verde e estrela vermelha. Esta lager puro malte holandesa é celebrada por seu sabor perfeitamente equilibrado e refrescância distinta. Notas frutadas sutis, um corpo leve e um final nítido fazem dela uma escolha sofisticada e universalmente apreciada.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(3, 'P003', 'Seleta', 34, 40.00, 'Cachaça Seleta (Garrafa) Explore um ícone da tradição mineira. Esta Cachaça de Alambique é a expressão autêntica de um sabor consagrado, maturada em tonéis de umburana que lhe conferem um buquê aromático único e notas levemente adocicadas. O resultado é um paladar suave e aveludado, uma escolha de prestígio para degustação pura ou para coquetéis que pedem um destilado com personalidade marcante.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(4, 'P004', 'Serramalte 600ml', 9, 13.00, 'Serramalte Pilsen (600ml) Um clássico reverenciado da escola cervejeira. Esta garrafa de 600ml entrega uma autêntica Pilsen de sabor maltado e aroma rico, mais encorpada que as lagers tradicionais. Sua coloração dourada profunda e amargor equilibrado a tornam a escolha ideal para quem aprecia uma cerveja robusta e cheia de personalidade.', 4.0, 'Ativo', NULL, NULL, NULL, NULL),
(5, 'P005', 'Chopp Brahma', 29, 11.50, 'Chopp Brahma (Claro) O clássico brasileiro servido com maestria. Nosso Chopp Brahma é tirado sob pressão ideal, resultando em uma bebida cristalina, de leveza incomparável e coroada pelo colarinho perfeitamente cremoso. O sabor refrescante e inconfundível para o brinde perfeito.', 4.0, 'Ativo', NULL, NULL, NULL, NULL),
(6, 'P006', 'Água c/ gás', 100, 12.00, NULL, 0.0, 'Ativo', NULL, NULL, NULL, NULL),
(7, 'P007', 'Água c/ gás e xarope de fruta', 80, 12.00, 'Soda Italiana Artesanal Uma fusão elegante de pura água gaseificada e xaropes de frutas premium. Esta bebida é uma alternativa sofisticada e não alcoólica, que equilibra perfeitamente a efervescência cristalina com notas vibrantes de sabor. Servida gelada para um refresco luminoso.', 3.0, 'Ativo', NULL, NULL, NULL, NULL),
(8, 'P008', 'Original 600ml', 60, 15.00, 'Antarctica Original (600ml) Uma Pilsen que preserva uma receita histórica. A Original é um ícone reconhecido por seu sabor suave, aroma discreto e uma refrescância autêntica, mantida desde 1931. Servida em sua clássica garrafa de 600ml, oferece uma experiência de paladar leve e notas sutis de malte, ideal para quem valoriza a tradição e a qualidade de um brinde clássico.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(9, 'P009', 'Campari', 50, 60.00, 'Campari (Garrafa) O inconfundível ícone do aperitivo italiano em sua forma plena. Esta garrafa icônica guarda a famosa receita de cor vermelha vibrante e sabor complexo, um equilíbrio perfeito entre o amargo, o herbal e o cítrico. Essencial para a coquetelaria de prestígio, é a alma de clássicos como o Negroni e o Americano.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(10, 'P010', 'Saqüé (caixinha)', 40, 12.00, 'Saquê no Masu (Dose Tradicional) Viva a autêntica experiência japonesa do masu. Servimos uma dose generosa de saquê premium em um copo que transborda sobre uma caixa de madeira de cedro, simbolizando hospitalidade e abundância. O aroma sutil da madeira complementa e enriquece as notas delicadas da bebida.', 4.0, 'Ativo', NULL, NULL, NULL, NULL),
(11, 'P011', 'Vodka Absolut', 25, 100.00, 'Vodka Absolut (Garrafa) Um ícone sueco de sabor excepcionalmente puro. Produzida exclusivamente com trigo de inverno e água pura, a Absolut é destilada continuamente, resultando em uma vodka de paladar rico, suave e limpo. A tela em branco perfeita para a coquetelaria criativa.', 4.5, 'Ativo', NULL, NULL, NULL, NULL),
(12, 'P012', 'Vodka Smirnoff', 30, 80.00, 'Vodka Smirnoff (Garrafa) O padrão mundial em pureza, triplamente destilada e filtrada dez vezes em carvão. A Smirnoff oferece um sabor excepcionalmente limpo e suave. É a escolha clássica e versátil, servindo como a base perfeita para elevar seus coquetéis favoritos.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(13, 'P013', 'Wisk 12 anos', 14, 200.00, 'Ballantine\'s 12 Anos (Garrafa) Um blend escocês de prestígio, maturado por no mínimo doze anos. Este whisky revela uma complexidade elegante com notas ricas de mel, baunilha e carvalho tostado, culminando em um final suave e cremoso. Uma escolha sofisticada para apreciadores de um sabor refinado.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(14, 'P014', 'Cachaça Boa Vista', 15, 18.00, 'Cachaça Boa Vista (Garrafa) Descubra um destilado que honra suas raízes com elegância. A Cachaça Boa Vista é uma expressão refinada da cana-de-açúcar, cuidadosamente produzida para entregar um paladar suave e ao mesmo tempo cheio de personalidade. Com aromas sutis e um final limpo, é uma bebida versátil, perfeita tanto para a degustação pura quanto para elevar seus coquetéis clássicos a um novo patamar de sofisticação.', 3.5, 'Ativo', NULL, NULL, NULL, NULL),
(15, 'P015', 'Cachaça São Francisco', 20, 20.00, 'Cachaça São Francisco (Garrafa) Explore a essência da hospitalidade brasileira com um verdadeiro clássico. A Cachaça São Francisco é um destilado consagrado, celebrado por sua pureza cristalina e um sabor autêntico que captura a alma do país. É a escolha tradicional e a base perfeita para a criação da caipirinha impecável, oferecendo um paladar limpo e vibrante que celebra a tradição em cada gole.', 4.0, 'Ativo', NULL, NULL, NULL, NULL),
(16, 'P016', 'Gin Tanqueray', 9, 150.00, 'Tanqueray London Dry (Garrafa) Aprecie o padrão definitivo dos gins London Dry. Tanqueray é uma lenda destilada, celebrada por sua receita icônica e perfeitamente equilibrada de quatro botânicos clássicos. Com o zimbro em primeiro plano, seguido por notas nítidas de coentro e cítricos, esta garrafa icônica é a base perfeita para os coquetéis mais sofisticados e a escolha definitiva para um G&T impecável.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(17, 'P017', 'Taça de Vinho', 50, 20.00, 'Seleção de Vinhos em Taça Descubra a expressão singular de terroirs renomados em nossa seleção de vinhos em taça. Cada rótulo foi cuidadosamente escolhido por nosso sommelier para oferecer uma experiência completa, desde o aroma complexo até o final persistente. Uma oportunidade perfeita para degustar vinhos de alta qualidade, explorar novas uvas ou simplesmente harmonizar seu prato com a taça ideal.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(18, 'P018', 'Garrafa de Vinho', 30, 25.00, 'Vinho Pérgola (Tinto / Branco Suave) Um verdadeiro ícone brasileiro que atravessa gerações. O Vinho Pérgola é a clássica escolha para momentos de confraternização, conhecido por seu paladar inconfundível e agradavelmente adocicado. Com notas frutadas evidentes, é uma bebida leve, descomplicada e que agrada facilmente, ideal para acompanhar massas, carnes leves ou simplesmente para celebrar a boa companhia.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(19, 'P019', 'Garrafa de Saquê', 8, 39.00, 'Nossa Seleção de Saquê (Garrafa) Descubra o auge da produção artesanal japonesa em nossa seleção premium de saquês. Cada garrafa oferece um paladar sofisticado e revela camadas complexas de sabor, de notas frutadas a um umami delicado. Uma experiência autêntica, ideal para harmonizar ou degustar.', 4.0, 'Ativo', NULL, NULL, NULL, NULL),
(20, 'P020', 'Gin Na Taça', 10, 35.00, 'Signature Gin na Taça Descubra a harmonia perfeita em nossa taça signature. Gin de alta qualidade infusionado lentamente com botânicos selecionados pelo nosso bartender, combinado com água tônica premium e gelo em cubos maciços. Uma bebida vibrante, aromática e incrivelmente refrescante.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(22, 'P021', 'Ice ', 50, 12.00, 'Smirnoff Ice (Long Neck) O ícone global da categoria \'ready-to-drink\', servido em sua garrafa inconfundível. Esta bebida combina a pureza clássica da vodka com a acidez vibrante do limão, criando uma experiência efervescente e perfeitamente equilibrada. A escolha definitiva para um refresco prático com um sabor nítido e sofisticado.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(23, 'P022', 'Ballena', 15, 140.00, 'Cerveja Ballena (1 Litro) A expressão máxima da confraternização, servida em formato generoso. Nossa seleção de 1 litro é apresentada na temperatura ideal, garantindo um brinde prolongado e um sabor que se mantém refrescante do início ao fim. Perfeita para a mesa farta e para celebrar os grandes momentos em excelente companhia.', 5.0, 'Ativo', NULL, NULL, NULL, NULL),
(24, 'P023', 'Água de côco de sabor', 100, 3.00, 'Água de Coco Aromatizada (Sabores) A hidratação tropical reimaginada com um toque de sofisticação. Nossa pura água de coco, naturalmente refrescante, é delicadamente infusionada com essências ou purês de frutas frescas. O resultado é uma bebida leve, vibrante e revigorante, que realça o sabor clássico do coco com novas notas aromáticas, criando um equilíbrio perfeito de doçura e frescor.', 5.0, 'Ativo', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(255) NOT NULL,
  `grupo` enum('Administrador','Estoquista') NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `cpf`, `email`, `grupo`, `senha`, `data_cadastro`, `status`) VALUES
(4, 'Estoquista', '867.276.590-79', 'estoquista@hotmail.com', 'Estoquista', '$2y$10$gXfvEllHyrPiG0lJzhKqc.IbooYW85mqIsyvp0AHAFSpq26kEiQtm', '2025-11-04 16:26:48', 'Ativo'),
(5, 'Cristiane', '002.605.960-60', 'cristiane_ronalda@gmail.com', 'Administrador', '$2y$10$Lnw3mBg24FPRanm9B.c4NuIO5n4Qq8A2bDYerNQl6RV.kX3zxk4ES', '2025-11-09 00:45:27', 'Ativo'),
(6, 'Admin', '879.470.090-74', 'admin@gmail.com', 'Administrador', '$2y$10$0u7ga4ZGB3sUWa4lV/blfOOLHdNxM083bcVjP8GaPmMSSrQSALEV2', '2026-03-23 22:14:43', 'Ativo'),
(7, 'estoq', '46542075038', 'estoquista@gmail.com', 'Estoquista', '$2y$10$22qLW6uagKJ3gwSiPOSbweI3ddiVzasKta0x3xA7Dcr04iUrA4Evi', '2026-03-23 22:40:01', 'Ativo');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `carrinho`
--
ALTER TABLE `carrinho`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `enderecos_entrega`
--
ALTER TABLE `enderecos_entrega`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `imagens_produto`
--
ALTER TABLE `imagens_produto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_pedido` (`numero_pedido`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `endereco_id` (`endereco_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `carrinho`
--
ALTER TABLE `carrinho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `enderecos_entrega`
--
ALTER TABLE `enderecos_entrega`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `imagens_produto`
--
ALTER TABLE `imagens_produto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `enderecos_entrega`
--
ALTER TABLE `enderecos_entrega`
  ADD CONSTRAINT `enderecos_entrega_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
