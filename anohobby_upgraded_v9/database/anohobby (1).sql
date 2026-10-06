-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 05, 2026 at 03:09 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anohobby`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`) VALUES
(1, 'Model Kit', 'model-kit', 'Plastic model kit yang dirakit sendiri - Gundam, Kamen Rider, One Piece, dll. Tersedia berbagai grade: MG, RG, HG, SD, PG. Cocok untuk pemula hingga expert builder.', 'https://i.pinimg.com/736x/7c/00/2d/7c002d0de241f50591e076b033058f98.jpg'),
(2, 'TCG Cards', 'tcg-cards', 'Trading Card Game koleksi & siap main: Yu-Gi-Oh, Pokemon, Magic The Gathering, One Piece Card, Bushiroad. Booster pack, structure deck, hingga booster box lengkap.', 'https://i.pinimg.com/736x/cc/a3/fb/cca3fb9f185b146a5f941aa9b73a2e95.jpg'),
(3, 'PVC Figure', 'pvc-figure', 'Koleksi PVC figure premium: Scale Figure 1/7 & 1/4, Nendoroid, Pop Up Parade, figma, Revoltech. Produk resmi dari Good Smile Company, KADOKAWA, Alter, Kotobukiya.', 'https://i.pinimg.com/1200x/26/44/a1/2644a19815240dd12d44890b4d6936aa.jpg'),
(4, 'Tools Rakit', 'tools-rakit', 'Peralatan merakit model kit: nipper GodHand/Tamiya, hobby knife, file, sanding sponge, tweezer, cutting mat. Semua yang dibutuhkan builder dari nol.', 'https://i.pinimg.com/1200x/6e/18/18/6e1818b32099a926b67a9e589d866b43.jpg'),
(5, 'Paint & Weathering', 'paint-weathering', 'Cat acrylic Tamiya/Gaia/Mr. Hobby, Gundam Marker untuk panel line, decal, weathering set, top coat. Lengkapi finishing modelkit biar makin realistic.', 'https://i.pinimg.com/736x/a1/dc/57/a1dc5789d30ee418bc239d14491b5390.jpg'),
(6, 'Action figure', 'action-figure', 'Figur yang memiliki engsel/persendian (articulated), sehingga posenya bisa diubah-ubah dan dipasang aksesoris (senjata, ekspresi wajah tambahan, dll).', 'https://i.pinimg.com/736x/68/0f/d1/680fd171c3cdf34cb69c718e78a552d7.jpg'),
(8, 'Hot Toys Series', 'hot-toys', 'Action figure koleksi skala presisi - Marvel, DC, Star Wars, dll. Tersedia dalam skala 1/6 dan 1/4 dengan detail super realistis, artikulasi fleksibel, serta kostum kain asli. Cocok untuk kolektor pemula hingga hardcore collector.', 'https://i.pinimg.com/736x/26/e3/14/26e31402e0f6f219cb45dfbbaac247bc.jpg'),
(9, 'Nendoroid', 'nendoroid', 'Figure chibi berbahan PVC dengan kepala besar dan bagian wajah serta aksesoris yang bisa diganti-ganti - Anime, Game, VTuber, dan Pop Culture. Tersedia seri standar, Nendoroid Swacchao!, hingga Nendoroid Doll. Cocok untuk kolektor pajangan imut hingga penggemar fotografi figure (toy photography).', 'https://i.pinimg.com/1200x/9e/fa/52/9efa52bcf10d05d6fb71a5769935b897.jpg'),
(10, 'Blokees', 'blokees', 'Miniatur karakter rakitan berbahan plastik dengan sistem snap-fit tanpa lem - Transformers, Ultraman, Marvel, dan Naruto. Tersedia berbagai lini seperti Galaxy (Blind Box), Classic, hingga Action Class. Cocok untuk pemula hingga kolektor figure artikulasi.', 'https://i.pinimg.com/1200x/5b/d5/1e/5bd51effc837eb0e55e73cd3e20693b4.jpg'),
(11, 'Diecast', 'diecast', 'Miniatur kendaraan berbahan logam presisi tinggi - Mobil, Motor, Pesawat, dan Alat Berat. Tersedia berbagai skala seperti 1/64, 1/43, hingga 1/18. Cocok untuk kolektor pajangan hingga pehobi fotografi miniatur.', 'https://i.pinimg.com/1200x/55/2a/49/552a4903c42d6030de6c318033bb642b.jpg'),
(12, 'Tamiya', 'tamiya', 'Miniatur mobil balap rakitan berbahan plastik yang digerakkan baterai dan motor listrik - Mini 4WD, RC, dan Scale Model. Tersedia berbagai pilihan chassis, dinamo, serta part modifikasi. Cocok untuk pemula hingga profesional racer/builder.', 'https://i.pinimg.com/1200x/03/5d/6f/035d6f6ee863d8c9b635d073ae1b4137.jpg'),
(13, 'Beyblade', 'beyblade', 'Permainan gasing bertarung modern berbilah logam/plastik yang diluncurkan lewat peluncur khusus - Seri Burst, X, Metal, hingga Klasik. Tersedia tipe petarung Attack, Defense, Stamina, dan Balance. Cocok untuk sarana bermain santai, ajang kumpul komunitas, hingga olahraga adu strategi di dalam arena.', 'https://i.pinimg.com/736x/c5/0e/b9/c50eb9c577cda646e94418855d0a758d.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL,
  `stock` int DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `badge` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flash_sale_ends_at` datetime DEFAULT NULL,
  `flash_sale_discount` int NOT NULL DEFAULT '0',
  `release_date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `image_2`, `image_3`, `image_4`, `image_5`, `images`, `badge`, `flash_sale_ends_at`, `flash_sale_discount`, `release_date`, `created_at`, `brand`) VALUES
(1, 1, 'MG 1/100 RX-78-2 Gundam Ver. 3.0', 'Master Grade model kit Bandai. Skala 1/100 dengan inner frame lengkap.', 798000.00, 5, 'https://down-id.img.susercontent.com/file/11987248d81e5efe5018363d8eca1b70@resize_w900_nl.webp', NULL, NULL, NULL, NULL, '', 'PRE-ORDER', NULL, 0, 'Release: Dec 2026', '2026-07-29 16:43:07', 'bandai'),
(2, 1, 'RG 1/144 Nu Gundam', 'Real Grade Nu Gundam dengan funnel effect parts.', 520000.00, 10, 'https://i.pinimg.com/1200x/fa/a1/c9/faa1c950680dc711f083bbc13d562314.jpg', 'https://i.pinimg.com/1200x/69/0a/e3/690ae3ae250a671ac6f52ff1c816515e.jpg', 'https://i.pinimg.com/1200x/35/c7/5e/35c75e60f68cc310638867d0d32c5542.jpg', NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', 'Bandai'),
(3, 1, 'HG Gundam Calibarn', 'HG gundam calibarn dari seri mobile suit gundam witch from mercury dengan scale 1/144', 280000.00, 15, 'https://i.pinimg.com/1200x/f1/16/94/f11694e220c5f0d2f3eafb4495fa69f8.jpg', 'https://i.pinimg.com/1200x/af/cc/9f/afcc9f5c3636c1bc8cdbf35a526fb3fa.jpg', 'https://i.pinimg.com/1200x/ef/18/cf/ef18cfa5a0de379c981362d817208344.jpg', NULL, NULL, NULL, NULL, NULL, 25, 'Ready Stock', '2026-07-29 16:43:07', 'Bandai'),
(4, 2, 'Pokemon TCG Booster Pack Scarlet & Violet', '1 pack berisi 10 kartu random + 1 energy.', 55000.00, 100, 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2023/02/SV1V_IDN_PRODUCT_THUMBNAIL.png', 'https://dz3we2x72f7ol.cloudfront.net/expansions/scarlet-violet/en-us/sv01-en-125-2x.png', 'https://dz3we2x72f7ol.cloudfront.net/expansions/scarlet-violet/en-us/sv01-en-081-2x.png', 'https://asia.pokemon-card.com/id/archive/special/card/sv1/assets/images/card-card-3.png', 'https://asia.pokemon-card.com/id/archive/special/card/sv1/assets/images/card-card-1.png', NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(5, 2, 'Freedom Ascension [GD05] gundam card game', 'set booster untuk merayakan ulang tahun pertama Gundam Card Game.\r\nMobile suit yang sangat populer, seperti Strike Freedom Gundam dan ν Gundam', 50000.00, 57, 'https://www.gundam-gcg.com/gcg/bccard/en/news/2026/04/30/rx2iVMb1gZkF1QUf/GD05_thumbnail_en.webp', 'https://www.gundam-gcg.com/gcg/bccard/en/news/2026/07/03/2MYHfNCwNhFBY9FM/batch_GD05-049_dummy_EN.webp', 'https://www.gundam-gcg.com/gcg/bccard/en/news/2026/07/03/R9KEhYs9489i3SSM/batch_GD05-067_dummy_EN.webp', 'https://www.gundam-gcg.com/gcg/bccard/en/news/2026/07/03/m2XtoLp6zYNgLFl0/batch_GD05-017_dummy_EN.webp', 'https://www.gundam-gcg.com/gcg/bccard/en/news/2026/07/03/sfnCbNW3EIFYGUgO/batch_GD05-002_dummy_EN.webp', NULL, NULL, NULL, 33, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(6, 2, 'Pokemon TCG Booster Pack \"Ancaman Bayangan\"', '1 booster pack pokemon TCG Ancaman bayangan yang berisi 5 kartu', 55000.00, 56, 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2026/05/idn_product_thumbnail_ma5_pkg.png', 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2026/06/idn_MA5_056.png', 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2026/05/IDN_MA5_071.png', 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2026/05/IDN_MA5_099.png', 'https://asia.pokemon-card.com/id/wp-content/uploads/sites/5/2026/05/IDN_MA5_038.png', NULL, NULL, NULL, 25, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(7, 3, 'PVC Figure 1/7 Gotoh Hitori - Live Ver. Bocchi the Rock!', 'PVC figure skala 1/7 dari KADOKAWA. Tinggi ~25cm.', 2850000.00, 1, 'https://kyoucdn.id/items/pvc-figure-17-gotoh-hitori-live-ver-bocchi-the-rock-e10c8aab.jpg.webp', 'https://kyoucdn.id/items/pvc-figure-17-gotoh-hitori-live-ver-bocchi-the-rock-bddcd655.jpg.webp', 'https://kyoucdn.id/items/pvc-figure-17-gotoh-hitori-live-ver-bocchi-the-rock-9e067beb.jpg.webp', NULL, NULL, NULL, 'PRE-ORDER', NULL, 0, 'Release: Mar 2027', '2026-07-29 16:43:07', NULL),
(8, 3, 'Ichiban Kuji Gracemaster Figure 1/7 Yamada Ryo - Bocchi the Rock! VOLUME 4 C Prize', 'Ichiban Kuji Gracemaster Figure 1/7 Yamada Ryo - Bocchi the Rock! VOLUME 4 C Prize', 1850000.00, 3, 'https://kyoucdn.id/items/447730-ichiban-kuji-gracemaster-figure-17-yamada-ryo-bocchi-the-rock-volume-4-c-prize-22cm.jpg.webp', 'https://kyoucdn.id/items/447807-ichiban-kuji-gracemaster-figure-17-yamada-ryo-bocchi-the-rock-volume-4-c-prize-22cm.jpg.webp', 'https://kyoucdn.id/items/447809-ichiban-kuji-gracemaster-figure-17-yamada-ryo-bocchi-the-rock-volume-4-c-prize-22cm.jpg.webp', 'https://kyoucdn.id/items/447808-ichiban-kuji-gracemaster-figure-17-yamada-ryo-bocchi-the-rock-volume-4-c-prize-22cm.jpg.webp', NULL, NULL, 'SALE', NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(9, 3, 'Pop Up Parade Makima Chainsaw Man', 'Pop Up Parade PVC figure, tinggi ~17cm.', 650000.00, 2, 'https://kyoucdn.id/items/176397-pop-up-parade-figure-makima-chainsaw-man.jpg.webp', 'https://kyoucdn.id/items/176445-pop-up-parade-figure-makima-chainsaw-man.jpg.webp', 'https://kyoucdn.id/items/176446-pop-up-parade-figure-makima-chainsaw-man.jpg.webp', 'https://kyoucdn.id/items/176448-pop-up-parade-figure-makima-chainsaw-man.jpg.webp', 'https://kyoucdn.id/items/176450-pop-up-parade-figure-makima-chainsaw-man.jpg.webp', NULL, 'HOT', NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(10, 4, 'GodHand Nipper SPN-120 Ultimate', 'nipper single edge premium dari Jepang. Sangat presisi.', 850000.00, 10, 'https://lascalemodel.com/cdn/shop/files/godspn-120_1.png?v=1692303980', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(11, 4, 'Tamiya Craft Tool Set Pro', 'Set 7pcs: nipper, knife, file, tweezer, dll.', 495000.00, 6, 'https://m.media-amazon.com/images/I/7100cG-xmzL._AC_UF1000,1000_QL80_.jpg', NULL, NULL, NULL, NULL, NULL, 'SALE', NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(12, 4, 'DSPIAE Sanding Sponge Set', 'Set 5 sponge grit 400-2000 untuk finishing halus.', 95000.00, 13, 'https://m.media-amazon.com/images/I/61Lu0xT1uFL.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(13, 5, 'Tamiya Acrylic Mini XF-1 Black (10ml)', 'Cat acrylic water-based. Tersedia berbagai warna.', 35000.00, 43, 'https://down-id.img.susercontent.com/file/9b6cf00a782590539866f08afebcf083@resize_w900_nl.webp', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(14, 5, 'Gundam Marker Panel Line GM01 Black', 'Marker untuk panel line, tahan air setelah kering.', 55000.00, 80, 'https://m.media-amazon.com/images/I/71ecI5qWypL._AC_UF894,1000_QL80_.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(15, 5, 'Mr. Weathering Color Sand Effect', 'Weathering set untuk efek pasir dan kotoran pada model.', 125000.00, 25, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoxMXEHIqF_S18tQkhJ4CPTt2gjGvR7AdmrV59CgB1dvkAS6Z0qqDmiKU&s=10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(16, 6, 'SHF Kamen Rider Zero one shining assault hopper', 'P-bandai action figure kamen rider zero one series, kamen rider zero one shining assault hopper', 1450000.00, 1, 'https://tamashiiweb.com/storage/images/products/imported/item_0000013204_T0g0JK4x_01.jpg', 'https://tamashiiweb.com/storage/images/products/imported/item_0000013204_T0g0JK4x_06.jpg', 'https://tamashiiweb.com/storage/images/products/imported/item_0000013204_T0g0JK4x_04.jpg', 'https://tamashiiweb.com/storage/images/products/imported/item_0000013204_T0g0JK4x_03.jpg', NULL, NULL, 'SALE', NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(17, 6, 'S.H.Figuarts Anby Demara - Soldier 0 Ver. Zenless Zone Zero', 'action figure SHF Tamashi nation Soldier 0 anby Zenless Zone Zero', 1750000.00, 5, 'https://kyoucdn.id/items/shf-shfiguarts-anby-demara-soldier-0-ver-zenless-zone-zero-e723b666ddb53f2a5a53d9901a2e56881fee859d631e77377723b69327c2d103.jpg.webp', 'https://kyoucdn.id/items/shf-shfiguarts-anby-demara-soldier-0-ver-zenless-zone-zero-aa1e5c194f82e937a97f0aa8355fe48939dfa51be463c94c3d0d9768a4625935.jpg.webp', 'https://kyoucdn.id/items/shf-shfiguarts-anby-demara-soldier-0-ver-zenless-zone-zero-cb2d9c5aa377280f204241cc8b1bdef56651ecd3f66db429ae7ffa56275e422e.jpg.webp', 'https://kyoucdn.id/items/shf-shfiguarts-anby-demara-soldier-0-ver-zenless-zone-zero-16dd5357ee6783b9ade77caa9a7aedc44d015cd57f49161c176526acea8d9ac5.jpg.webp', 'https://kyoucdn.id/items/shf-shfiguarts-anby-demara-soldier-0-ver-zenless-zone-zero-6e256189bb4dc159af1b50502c70845ec8c2bfe8fdf3cf25ab2c34d3057d65da.jpg.webp', NULL, 'PRE-ORDER', NULL, 0, 'Release: Mar 2027', '2026-07-29 16:43:07', NULL),
(18, 6, 'Revoltech Amazing Yamaguchi Action Figure Arkham Knight Ver. 1.5 - Batman Arkham Night', 'Height: 17cm\r\nScukptor: Kasuhisa Yamaguchi / Yoshiaki Yu\r\nMaterial: PVC, ABS, POM\r\nAccessories: ・2 x handguns\r\n・1 x rifle\r\n・6 x optional hands\r\n・1 x Red Hood helmet part\r\n・2 x chest parts\r\n・2 x shoulder armor parts\r\n・2 x upper arm parts\r\n・2 x knee armor parts\r\n・2 x forearm decoration parts\r\n・2 x\r\nknives・2 x swords\r\n・1 ​​x display stand', 1980000.00, 8, 'https://kyoucdn.id/items/318728-with-bonus-revoltech-amazing-yamaguchi-arkham-knight-ver-15-batman-arkham-night.jpg.webp', 'https://kyoucdn.id/items/318742-with-bonus-revoltech-amazing-yamaguchi-arkham-knight-ver-15-batman-arkham-night.jpg.webp', 'https://kyoucdn.id/items/318743-with-bonus-revoltech-amazing-yamaguchi-arkham-knight-ver-15-batman-arkham-night.jpg.webp', 'https://kyoucdn.id/items/318745-with-bonus-revoltech-amazing-yamaguchi-arkham-knight-ver-15-batman-arkham-night.jpg.webp', 'https://kyoucdn.id/items/318746-with-bonus-revoltech-amazing-yamaguchi-arkham-knight-ver-15-batman-arkham-night.jpg.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(22, 3, 'PVC Figure Gift+ 1/8 Hoshimi Miyabi - 2025 FES Sparkling Wonderland Ver. Zenless Zone Zero', 'Material: PVC ABS\r\nSize: about 22.6cm', 965000.00, 3, 'https://kyoucdn.id/items/550538-pvc-figure-gift-18-hoshimi-miyabi-2025-fes-sparkling-wonderland-ver-zenless-zone-zero-1130135181.jpg.webp', 'https://kyoucdn.id/items/551693-pvc-figure-gift-18-hoshimi-miyabi-2025-fes-sparkling-wonderland-ver-zenless-zone-zero.jpg.webp', 'https://kyoucdn.id/items/551694-pvc-figure-gift-18-hoshimi-miyabi-2025-fes-sparkling-wonderland-ver-zenless-zone-zero.jpg.webp', 'https://kyoucdn.id/items/551696-pvc-figure-gift-18-hoshimi-miyabi-2025-fes-sparkling-wonderland-ver-zenless-zone-zero.jpg.webp', 'https://kyoucdn.id/items/551697-pvc-figure-gift-18-hoshimi-miyabi-2025-fes-sparkling-wonderland-ver-zenless-zone-zero.jpg.webp', NULL, 'HOT', NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(23, 8, 'Hot Toys Superior SpiderMan suit', 'Hot Toys VGM61 Marvel\'s Spider-Man 2 Peter Parker (Superior Suit) Action Figure', 9345000.00, 3, 'https://down-id.img.susercontent.com/file/id-11134207-8224y-mkae51uoem886b.webp', 'https://down-id.img.susercontent.com/file/id-11134207-82252-mk91ysce8u0zf4.webp', 'https://down-id.img.susercontent.com/file/id-11134207-8224o-mkae51uoaeiwd2.webp', NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(24, 8, 'Hot Toys Ironman Mark 42 XLII 2.0 Deluxe Tony Stark', 'Hot toys 1:6 iron man mark 42 from iron man 3 movie', 10125000.00, 2, 'https://down-id.img.susercontent.com/file/id-11134207-82250-mk7j4250t2wz05.webp', 'https://down-id.img.susercontent.com/file/id-11134207-8224z-mk7j4250uhhf77.webp', 'https://www.sideshow.com/cdn-cgi/image/quality=90,f=auto/https://www.sideshow.com/storage/product-images/913628/iron-man-mark-xlii-20_marvel_gallery_669fd798ead82.jpg', NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-07-29 16:43:07', NULL),
(25, 9, 'Nendoroid Godzilla (2023) - Godzilla Minus One', 'Painted plastic non-scale articulated figure with stand included. Approximately 100mm in height.\r\n\r\nFrom the film \"Godzilla Minus One\" comes Nendoroid Godzilla (2023)! It features jaws that can be opened and closed as well as articulated eyes.', 980000.00, 7, 'https://kyoucdn.id/items/nendoroid-godzilla-2023-godzilla-minus-one-a8f97678.jpg.webp', 'https://kyoucdn.id/items/nendoroid-godzilla-2023-godzilla-minus-one-9f2ecab8.jpg.webp', 'https://kyoucdn.id/items/nendoroid-godzilla-2023-godzilla-minus-one-e39c0557.jpg.webp', 'https://kyoucdn.id/items/nendoroid-godzilla-2023-godzilla-minus-one-755be815.jpg.webp', 'https://kyoucdn.id/items/nendoroid-godzilla-2023-godzilla-minus-one-c1586523.jpg.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-10 05:44:37', NULL),
(26, 9, 'Nendoroid Rathalos - Monster Hunter', 'Painted plastic non-scale articulated figure with stand included. Approximately 90mm in height.\r\n\r\nFrom the popular \"Monster Hunter\" game series comes a Nendoroid of the iconic monster, Rathalos!', 1070000.00, 4, 'https://kyoucdn.id/items/nendoroid-rathalos-monster-hunter-be80dcb7.jpg.webp', 'https://kyoucdn.id/items/nendoroid-rathalos-monster-hunter-f2d8b9ef.jpg.webp', 'https://kyoucdn.id/items/nendoroid-rathalos-monster-hunter-cc0a6153.jpg.webp', 'https://kyoucdn.id/items/nendoroid-rathalos-monster-hunter-7abf0fad.jpg.webp', 'https://kyoucdn.id/items/nendoroid-rathalos-monster-hunter-4153953d.jpg.webp', NULL, 'PRE-ORDER', NULL, 0, 'Release: Feb 2027', '2026-08-10 05:47:08', NULL),
(27, 1, 'Alaya 02 MGSD Barbatos Lupus Rex GK Style', 'Alaya 02 MGSD Barbatos Lupus Rex GK Style third party Model Kit from mobile suit gundam: iron blooded orphan', 545000.00, 8, 'https://down-id.img.susercontent.com/file/id-11134207-822wq-mnzaud75xu691a.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wq-mnzaud6wavwj6c.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wh-mnzby6edqnsx43.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wi-mnzaud75wflt18.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wq-mnzaud760nb598.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-10 06:27:07', 'Third Party'),
(28, 9, 'Nendoroid Yamada Ryo - Casual Clothes Ver. Bocchi The Rock!', 'Painted plastic non-scale articulated figure with stand included. Approximately 100mm in height.\r\n\r\nFrom \"Bocchi the Rock!\" comes a Nendoroid of Ryo Yamada in her outfit from the 9th episode!', 745000.00, 4, 'https://kyoucdn.id/items/375443-nendoroid-yamada-ryo-shifuku-ver-bocchi-the-rock-1570022631.jpg.webp', 'https://kyoucdn.id/items/404002-nendoroid-yamada-ryo-shifuku-ver-bocchi-the-rock.jpg.webp', 'https://kyoucdn.id/items/404004-nendoroid-yamada-ryo-shifuku-ver-bocchi-the-rock.jpg.webp', 'https://kyoucdn.id/items/404003-nendoroid-yamada-ryo-shifuku-ver-bocchi-the-rock.jpg.webp', 'https://kyoucdn.id/items/404005-nendoroid-yamada-ryo-shifuku-ver-bocchi-the-rock.jpg.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-10 06:34:44', NULL),
(29, 10, 'Blokees Model Kit Action Edition Evangelion Production Model 01 / EVA-01 - TV Ver', 'Blokees Model Kit Action Edition Evangelion Production Model 01 (EVA-01) - TV Version.\r\n\r\nKondisi: Baru / Original (BNIB)\r\n\r\nSeri: Action Edition (TV Version)\r\n\r\nKarakter: Evangelion Unit-01 (EVA-01)\r\n\r\nFitur:\r\n\r\nSystem snap-fit (dapat dirakit tanpa menggunakan lem)\r\n\r\nMemiliki artikulasi sendi untuk penyesuaian pose\r\n\r\nDetail dan skema warna presisi sesuai versi serial TV\r\n\r\nKelengkapan: Set part model kit, aksesori/senjata ikonik, dan buku panduan perakitan\r\n\r\nStok ready. Silakan diorder.', 440000.00, 10, 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-action-edition-evangelion-production-model-01-eva-01-tv-ver-85d5ab89.jpg', 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-action-edition-evangelion-production-model-01-eva-01-tv-ver-5e877589.jpg', 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-action-edition-evangelion-production-model-01-eva-01-tv-ver-42c6045e.jpg', 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-action-edition-evangelion-production-model-01-eva-01-tv-ver-79e0cc35.jpg', 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-action-edition-evangelion-production-model-01-eva-01-tv-ver-527a9153.jpg', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 03:02:59', NULL),
(30, 10, 'Blokees Model Kit Transformers Classic Class Megatronus / The Fallen - Transformers ONE', 'Deskripsi Produk:\r\n\r\nBlokees Model Kit Transformers Classic Class 20 Megatronus / The Fallen - Transformers ONE\r\n\r\nKondisi: Baru / Original (BNIB, Produk Berlisensi Resmi Hasbro)\r\n\r\nSpesifikasi & Fitur:\r\n\r\nTinggi Figur: Sekitar 12,5 cm\r\n\r\nSistem Rakit: Snap-fit (dapat dirakit tanpa lem atau alat pemotong)\r\n\r\nFitur Utama: Fitur LED menyala pada bagian mata dan dada\r\n\r\nArtikulasi: Fully poseable (20+ titik sendi artikulasi)\r\n\r\nJumlah Part: ~107 pcs\r\n\r\nKelengkapan Box: Set part model kit, aksesori senjata khusus, hand-parts tambahan, display stand, dan buku panduan perakitan\r\n\r\nStok siap kirim. Silakan diorder.', 190000.00, 13, 'https://kyoucdn.id/items/449460-blokees-model-kit-transformers-classic-class-2-cc-20-movie-8-megatronus-the-fallen-transformers-one.jpg.webp', 'https://kyoucdn.id/items/449560-blokees-model-kit-transformers-classic-class-2-cc-20-movie-8-megatronus-the-fallen-transformers-one.jpg.webp', 'https://kyoucdn.id/items/449561-blokees-model-kit-transformers-classic-class-2-cc-20-movie-8-megatronus-the-fallen-transformers-one.jpg.webp', 'https://kyoucdn.id/items/449562-blokees-model-kit-transformers-classic-class-2-cc-20-movie-8-megatronus-the-fallen-transformers-one.jpg.webp', NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 03:05:51', NULL),
(31, 10, 'Blokees Model Kit Champion Class Moon Knight', 'Model kit resmi karakter Moon Knight dari Marvel. Mengusung sistem perakitan snap-fit yang praktis tanpa memerlukan lem, cocok untuk perakit pemula maupun kolektor figur pahlawan super.\r\n\r\nHasil rakitan menampilkan detail jubah dan tekstur kostum ikonik yang presisi sesuai versi komik/serialnya, dilengkapi artikulasi sendi yang fleksibel untuk berbagai pose aksi dinamis, serta kelengkapan aksesori senjata khas seperti Crescent Dart.\r\n\r\nStok ready. Silakan diorder.', 260000.00, 9, 'https://kyoucdn.id/cdn-cgi/image/format=webp,quality=82,width=1200/items/blokees-model-kit-champion-class-10-cc-10-moon-knight-marvel-rivals-1f8c5c8ee7b8dd7d280d9932197ed012adedd7fab3faba03793df392aa501c8c.jpg', 'https://kyoucdn.id/items/blokees-model-kit-champion-class-10-cc-10-moon-knight-marvel-rivals-dce486ffec2ec8f78a7840be1a26286b571b64b4a8b0f09a6ef13759964bc252.jpg.webp', 'https://kyoucdn.id/items/blokees-model-kit-champion-class-10-cc-10-moon-knight-marvel-rivals-1b3f08d4167e9957dcf37caeff7d0db59e726c18506556f8cd8ab57505d55ebf.jpg.webp', 'https://kyoucdn.id/items/blokees-model-kit-champion-class-10-cc-10-moon-knight-marvel-rivals-612922cbc31d147d28a0861628217e6f3c4559f9a368243003cb31058a22359a.jpg.webp', 'https://kyoucdn.id/items/blokees-model-kit-champion-class-10-cc-10-moon-knight-marvel-rivals-2729bc2315f948bd8eeac8259eca9abfb35922512014e962bbd723c7462160d5.jpg.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 03:27:19', NULL),
(32, 11, 'MINI GT MAZDA RX-7 LB-Super Silhouette Liberty Walk Black', 'MINI GT Mazda RX-7 LB-Super Silhouette Liberty Walk Black\r\n\r\nDiecast miniatur skala 1:64 resmi dari MINI GT yang mereplikasi mobil legendaris Mazda RX-7 dengan sentuhan widebody kit ekstrem gaya LB-Super Silhouette khas Liberty Walk. Balutan warna hitam (black) memberikan tampilan yang garang, elegan, dan bold.\r\n\r\nModel ini dirancang dengan presisi tinggi menggunakan bodi berbahan diecast metal, ban berbahan karet yang dapat berputar, serta detail interior dan eksterior yang sangat rapat—mulai dari spoiler belakang besar, splitter depan, hingga logo decal sponsor yang akurat. Cocok untuk melengkapi koleksi diecast skala 1:64 maupun pemajang bertema JDM/tuning culture.\r\n\r\nStok ready. Silakan diorder.', 140000.00, 5, 'https://minigt.tsm-models.com/upload/picfile_list/790436831543d9e73a07cc9293fcc19020260519183153050.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/81ccec5baef2e7de81dd0f96a1033bf620260211185516386.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/75b7004e3d9c7ca34c31ea89e8737f5020260211185516391.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/7f743f178c9df8a26311a2322cac94aa20260211185516393.JPG', NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 03:40:59', NULL),
(33, 11, 'Mini GT 1/64 Nissan LB-Super Silhouette S15 SILVIA Auto Finesse SEMA 2023', 'MINI GT 1/64 Nissan LB-Super Silhouette S15 SILVIA Auto Finesse SEMA 2023\r\n\r\nDiecast miniatur skala 1:64 resmi dari MINI GT yang mereplikasi Nissan Silvia S15 dengan racikan widebody kit LB-Super Silhouette dari Liberty Walk, yang tampil khusus pada ajang SEMA Show 2023 dalam kolaborasi bersama Auto Finesse.\r\n\r\nModel ini hadir dengan bodi berbahan diecast metal, ban karet yang dapat berputar, serta detail interior dan eksterior yang sangat presisi. Tampilan luar memperlihatkan livery khas Auto Finesse yang akurat, aero-kit ekstrem, spoiler belakang besar, hingga detail Velg dan decal sponsor sesuai versi mobil aslinya. Cocok untuk kolektor diecast skala 1:64, penggemar JDM, maupun pengagum kultur modifikasi Liberty Walk.\r\n\r\nStok ready. Silakan diorder.', 155000.00, 2, 'https://minigt.tsm-models.com/upload/picfile_list/c35a8d079a065cf9d6ff6238e0ec82f820240908041712443.jpg', 'https://minigt.tsm-models.com/upload/picfile_list/1db31b925da249f3e52130d79288881820240628004302545.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/13b2c1afb61a457edfc4a28178cd2a3920240628004302547.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/d9d365d98f7044b26524aaf49e4bee1a20240628004302548.JPG', NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 06:23:03', NULL),
(34, 11, 'MINI GT LB-Super Silhouette Nissan SILVIA (S15)“GARUDA” MINI GT x MIZU Diecast', 'Diecast miniatur skala 1:64 resmi dari MINI GT hasil kolaborasi eksklusif bersama MIZU Diecast. Model ini mereplikasi basis Nissan Silvia S15 berbalut widebody kit ekstrem LB-Super Silhouette dari Liberty Walk, yang dipercantik dengan livery grafis bernuansa motif Burung Garuda.\r\n\r\nMenggunakan bodi berbahan diecast metal, ban karet yang dapat berputar, serta kepresisian detail interior dan eksterior yang rapi—mulai dari aero-kit lebar, wing belakang besar, hingga grafis decal bertema Garuda yang tajam. Sangat cocok sebagai item koleksi istimewa bagi para kolektor diecast 1:64 dan penggemar kultur modifikasi JDM/Liberty Walk.\r\n\r\nStok ready. Silakan diorder.', 150000.00, 5, 'https://minigt.tsm-models.com/upload/picfile_list/43ac71c9f8a4611a665f478c75e36b9320240808060652314.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/4bb7dc505aa762a1748301e7127612fe20231120075038173.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/920e7eafb84e068a7d9b44d7a4c467b820231120075038174.JPG', 'https://minigt.tsm-models.com/upload/picfile_list/51ee737d0be548e58842de00409b4fe320231120075038174.JPG', NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 06:30:28', NULL),
(35, 12, 'TAMIYA 19440 CYCLONE MAGNUM PREMIUM', 'TAMIYA 19440 CYCLONE MAGNUM PREMIUM (AR CHASSIS)\r\n\r\nModel kit Mini 4WD original dari Tamiya yang mereplikasi mobil ikonik Cyclone Magnum milik karakter Gou Seiba dari serial animasi populer Bakusou Kyoudai Let\'s & Go!!. Versi \"Premium\" ini hadir dengan pembaruan sasis modern untuk performa lintasan yang lebih stabil dan responsif.\r\n\r\nSasis: AR Chassis (bahan ABS warna hitam, under-panel dan diffuser warna biru)\r\n\r\nBodi: Polikarbonat/ABS warna putih dengan desain aerodynamic cowl dan stiker metallic tahan air\r\n\r\nDapur Pacu: Menggunakan mesin/motor Dinamo standar (termasuk dalam kemasan)\r\n\r\nRoda & Ban: Velg diameter besar (large diameter) warna hijau fluoresen dipadu ban karet profil tipis (low-profile) warna hitam\r\n\r\nSistem Rakit: Snap-fit tanpa menggunakan lem\r\n\r\nStok ready. Silakan diorder.', 230000.00, 15, 'https://d7z22c0gz59ng.cloudfront.net/japan_contents/img/usr/item/1/19440/19440_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 06:49:53', NULL),
(36, 12, 'TAMIYA 19444 Beat Magnum PREMIUM AR Chassis', 'TAMIYA 19441 BEAT-MAGNUM PREMIUM (AR CHASSIS)\r\n\r\nModel kit Mini 4WD original dari Tamiya yang mereplikasi mobil legendaris generasi keempat milik Gou Seiba dari serial Bakusou Kyoudai Let\'s & Go!!. Versi \"Premium\" ini hadir dengan desain cowl aerodinamis khas Beat-Magnum serta peningkatan performa menggunakan sasis modern.\r\n\r\nSasis: AR Chassis (bahan ABS warna hitam, under-panel dan diffuser warna biru)\r\n\r\nBodi: ABS warna putih dengan detail dragon suspension ikonik pada bodi belakang dan stiker metallic\r\n\r\nDapur Pacu: Menggunakan mesin/motor Dinamo standar (termasuk dalam kemasan)\r\n\r\nRoda & Ban: Velg diameter besar (large diameter) warna hijau fluoresen dipadu ban profil tipis (low-profile) warna hitam\r\n\r\nStok ready. Silakan diorder.', 270000.00, 5, 'https://d7z22c0gz59ng.cloudfront.net/japan_contents/img/usr/item/1/19444/19444_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 06:55:02', NULL),
(37, 12, 'tamiya super avante rs (ms chassis)', 'Model kit Mini 4WD edisi khusus variasi MS Chassis yang menggabungkan desain bodi aerodinamis khas Super Avante dengan sistem mesin tengah (mid-ship motor) untuk keseimbangan dan stabilitas tinggi di lintasan.\r\n\r\nSasis: MS Chassis (sistem 3-bagan dengan susunan mesin double-shaft di bagian tengah)\r\n\r\nBodi: ABS warna biru metalik dengan aksen stiker racing bernuansa modern\r\n\r\nDapur Pacu: Menggunakan mesin/motor Dinamo double-shaft standar (termasuk dalam kemasan)\r\n\r\nRoda & Ban: Velg diameter besar warna perak (silver) dipadu ban profil tipis warna hitam\r\n\r\nSistem Rakit: Snap-fit tanpa menggunakan lem\r\n\r\nStok ready. Silakan diorder.', 185000.00, 6, 'https://d7z22c0gz59ng.cloudfront.net/japan_contents/img/usr/item/1/18065/18065_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 06:57:35', NULL),
(38, 13, 'Takara tomy beyblade burst savior valkriye', 'Beyblade tipe Attack putaran kanan dari lini Beyblade Burst Dynamite Battle (DB). Mengusung karakter ikonik Valkyrie yang dipergunakan oleh Valt Aoi, dilengkapi dengan sistem bound/spring action pada layer untuk memberikan daya hantam yang sangat kuat.\r\n\r\nKondisi: Baru / Original Takara Tomy (BNIB)\r\n\r\nSeri: Beyblade Burst DB (Dynamite Battle) / System DB\r\n\r\nTipe: Attack (Putaran Kanan / Right-Spin)\r\n\r\nKelengkapan Box: Layer (Savior Valkyrie-7), Driver (Shot), Custom Sticker Set, Custom Launcher/Pelontar (Power Custom Sword Launcher LR), dan Buku Panduan.\r\n\r\nStok ready. Silakan diorder.', 280000.00, 6, 'https://m.media-amazon.com/images/I/71705QxengL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/71mk4YbGW0L._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/616+7rOH1vL._AC_SX679_.jpg', NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:05:07', NULL),
(39, 13, 'TAKARA TOMY B-110 Beyblade Burst Bloody Longinus', 'Beyblade tipe Attack putaran kiri (left-spin) dari lini Beyblade Burst Turbo / Cho-Z. Dikenal sebagai Beyblade milik karakter Lui Shirosagijo, mengusung desain kepala naga perak yang agresif untuk menghasilkan serangan bertubi-tubi di arena.\r\n\r\nKondisi: Baru / Original Takara Tomy (BNIB)\r\n\r\nSeri: Beyblade Burst Cho-Z (System Cho-Z Layer)\r\n\r\nTipe: Attack (Putaran Kiri / Left-Spin)\r\n\r\nStok ready. Silakan diorder.', 260000.00, 4, 'https://m.media-amazon.com/images/I/61mCCHbhUmL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/61Q1WzXwazL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/61BD780p8VL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/61f8ZGz+OAL._AC_SL1100_.jpg', NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:14:01', NULL),
(40, 13, 'Takara tomy beyblade burst Venom Diabolos Vanguard Bullet', 'TAKARA TOMY B-145 BEYBLADE BURST VENOM DIABOLOS .Vn.Bl (WITH DRAGON CHIP)\r\n\r\nBeyblade tipe Balance dari lini Beyblade Burst Rise / GT (Gachi). Dikenal sebagai Beyblade milik karakter Delta Akane, mengusung fitur unik Dual Spin serta Driver khusus yang dapat membelah diri di arena untuk menciptakan pertarungan 2-lawan-1.\r\n\r\nKondisi: Baru / Original Takara Tomy (BNIB)\r\n\r\nSeri: Beyblade Burst GT (Gachi Layer System)\r\n\r\nTipe: Balance\r\n\r\nKelengkapan Box: Layer (Venom Diabolos), Extra Base (Erase Base untuk putaran kiri), Disc (Vanguard), Driver (Bullet), Dragon Winder Launcher (LR Custom Launcher), Custom Sticker Set, dan Buku Panduan.\r\n\r\nStok ready. Silakan diorder.', 320000.00, 5, 'https://m.media-amazon.com/images/I/61oTsJeX2lL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/71K3t9lm+vL._AC_SX679_.jpg', 'https://m.media-amazon.com/images/I/61xpv+oWEBL._AC_SX679_.jpg', NULL, NULL, NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:25:11', NULL),
(41, 1, 'SNAA Thunder incise grace BE 1/144', 'SNAA SC-006 THUNDER INCISE GRACE (1/100 SCALE PLASTIC MODEL KIT)\r\n\r\nModel kit mecha berskala 1/100 original IP dari lini Knights of the Round Table buatan SNAA (Super Nova Art & Association). Mengusung estetika ksatria mekanis modern dengan proporsi bodi tajam, artikulasi tinggi, serta detail panel line yang presisi tanpa memerlukan lem.\r\n\r\nSkala & Kategori: 1/100 Scale (setara Master Grade / MG)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Inner Frame mandiri yang kokoh dan artikulatif\r\n\r\nDesain & Warna: Bodi bernuansa ksatria tempur futuristik dengan perpaduan warna dominan putih, aksen abu-abu, ungu/biru, serta detail stiker/decal reflektif\r\n\r\nPersenjataan & Aksesori:\r\n\r\nSenjata utama berupa tombak/pedang ksatria besar (Great Lance/Sword)\r\n\r\nPerisai pelindung (Shield) dengan artikulasi dinamis\r\n\r\nEfek partikel bening (clear parts) dan water decal untuk memperdetil tampilan bodi\r\n\r\nArtikulasi: Joint fleksibel pada bahu, pinggang, dan kaki yang mendukung berbagai pose tempur ekstrim\r\n\r\nStok ready. Silakan diorder.', 180000.00, 5, 'https://www.inaboxstore.com/cdn/shop/files/snaa-super-nova-beyond-exquisite-be-thunder-incise-grace-the-round-table-knights-8043154.jpg?v=1781369536', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-006-02-scaled.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-006-00-scaled-1024x1820.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-006-04-1024x576.jpg', 'https://modelhttps://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-006-07-1024x576.jpgverse.co.uk/wp-content/uploads/2025/07/snaa-sc-006-08-scaled-1024x1820.jpg', NULL, 'SALE', NULL, 0, 'Ready Stock', '2026-08-11 07:36:24', 'Mochin'),
(42, 1, 'SNAA Gods’ Guardian Gawain BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) buatan SNAA. Kit ini mengusung tingkat presisi dan articulasi setara kasta Real Grade (RG) dengan struktur Inner Frame penuh.\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan Ultimate Movable Full Body Skeleton (Inner Frame mandiri)\r\n\r\nDesain & Fitur:\r\n\r\nSeparasi warna part tajam tanpa butuh banyak cat (Detailed Color Separation)\r\n\r\nEfek sayap cahaya transparan (Special Light Wings Effect Parts)\r\n\r\nPersenjataan & Aksesori:\r\n\r\nPedang besar yang dapat ditransformasi menjadi senapan (Transforming Sword/Rifle)\r\n\r\nPerisai (Shield)\r\n\r\nWater Slide Decals & set tangan opsional\r\n\r\nStok ready. Silakan diorder.', 245000.00, 2, 'https://www.gundam.my/detailimage/big/8881/image_8881.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-003-kk-00-scaled.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-003-kk-01-1024x768.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-003-kk-08-scaled-1024x1365.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-003-kk-07-1024x768.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:43:22', 'Mochin'),
(43, 1, 'SNAA Aegis Knight Achilles BE 1/144', 'SNAA SC-005 AEGIS KNIGHT ACHILLES (1/144 BE SERIES)\r\n\r\nModel kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) buatan SNAA. Mengusung konsep ksatria pertahanan bertulang rangka penuh (Full Inner Frame) dengan tingkat detail dan artikulasi presisi setara kasta Real Grade (RG) dalam ukuran yang lebih kompak.\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan Ultimate Movable Inner Frame yang kokoh\r\n\r\nDesain & Fitur:\r\n\r\nSeparasi warna part tajam tanpa perlu mengecat ulang (Detailed Color Separation)\r\n\r\nProporsi armor ksatria taktis dengan detail panel line yang sudah tercetak presisi\r\n\r\nPersenjataan & Aksesori:\r\n\r\nPerisai khas Aegis Shield berukuran besar dengan artikulasi dinamis\r\n\r\nTombak/Pedang Ksatria (Tactic Lance/Sword)\r\n\r\nSet tangan opsional & Water Slide Decals\r\n\r\nStok ready. Silakan diorder.', 215000.00, 6, 'https://www.gundam.my/images/sell_products/big/image_9159.jpg', 'https://modelverse.co.uk/wp-content/uploads/2026/01/snaa-sc-004-00.webp', 'https://modelverse.co.uk/wp-content/uploads/2026/01/snaa-sc-004-01.webp', 'https://modelverse.co.uk/wp-content/uploads/2026/01/snaa-sc-004-05.webp', 'https://modelverse.co.uk/wp-content/uploads/2026/01/snaa-sc-004-07.webp', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:47:29', 'Mochin'),
(44, 1, 'SNAA Iron sickie kay BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Julukannya White Death Reaper, mengusung desain ksatria penuai nyawa yang lincah dengan artikulasi tinggi dan presisi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Siluet bodi taktis dan aerodinamis khas ksatria penyerang cepat dengan separasi warna part yang tajam\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Heavy Iron Sickle (sabit raksasa utama)\r\n\r\n2x Small Scythes / Sub-Sickles (sabit kecil opsional yang bisa dipasang di punggung)\r\n\r\n1x Defense Shield (perisai pelindung)\r\n\r\nWater Slide Decals & stiker foil aluminium reflektif untuk detail mata/monitor\r\n\r\nStok ready. Silakan diorder.', 240000.00, 4, 'https://down-id.img.susercontent.com/file/id-11134207-822wu-mpdo4u6wv4emd3@resize_w900_nl.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-007-01-1024x768.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-007-03-1024x768.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-007-07-1024x768.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-007-08-scaled-1024x1365.jpg', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 07:50:53', 'Mochin'),
(45, 1, 'SNAA Giant Axe Lancelot BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Karakter komandan bertema ksatria tangguh ini dipersenjatai kapak raksasa dengan tingkat presisi dan artikulasi tinggi setara kasta Real Grade (RG).Skala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)Tinggi Figur: ±14 cm (standar proporsi 1/144)  Konstruksi: Snap-fit plastic model kit dengan Ultimate Movable Full Body Skeleton (Inner Frame mandiri yang kokoh)  Desain & Warna: Tampilan ksatria tempur lapis baja dengan ciri khas helm bertanduk (horned helmet) serta kombinasi separasi warna part yang tajam tanpa perlu dirakit dengan catPersenjataan & Aksesori:1x Giant Axe (kapak raksasa ikonik yang dapat dipasang di tangan atau dipunggung)1x Great Sword / Pedang KsatriaBackpack Unit & Wing BoosterWater Slide Decals & set tangan opsionalStok ready. Silakan diorder.', 320000.00, 2, 'https://www.gundam.my/images/sell_products/big/image_9285.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-005-05-1024x683.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-005-06-1024x683.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-005-08-1024x683.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/07/snaa-sc-005-10-1024x683.webp', NULL, 'HOT', NULL, 0, 'Ready Stock', '2026-08-11 08:00:54', 'Mochin'),
(46, 1, 'SNAA Titan great sword tristan BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Mengusung konsep ksatria pedang raksasa dengan artikulasi fleksibel dan presisi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Siluet armor ksatria taktis bernuansa tempur dengan separasi warna part yang tajam dan presisi\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Titan Great Sword (pedang raksasa utama dengan opsi penggabungan/transformasi)\r\n\r\n1x Heavy Shield / Perisai Pelindung\r\n\r\nSet tangan opsional untuk berbagai gaya memegang senjata\r\n\r\nWater Slide Decals & stiker reflektif untuk detail bodi\r\n\r\nStok ready. Silakan diorder.', 315000.00, 2, 'https://www.gundam.my/detailimage/big/9564/image_9564.jpg', 'https://modelverse.co.uk/wp-content/uploads/2025/10/SNAA-SC-002-00-scaled.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/10/SNAA-SC-002-02-scaled.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/10/SNAA-SC-002-03-2048x1365.webp', 'https://modelverse.co.uk/wp-content/uploads/2025/10/SNAA-SC-002-04-scaled.webp', NULL, NULL, NULL, 72, 'Ready Stock', '2026-08-11 08:12:04', 'Mochin'),
(47, 1, 'MG Gundam vidar bootleg Tiger model', 'TIGER MODEL 1/100 MG GUNDAM VIDAR (PLASTIC MODEL KIT)\r\n\r\nModel kit mecha berskala 1/100 (Master Grade) dari lini Mobile Suit Gundam Iron-Blooded Orphans hasil rilis produsen china Tiger Model. Mengusung desain ikonik Gundam Vidar lengkap dengan struktur rangka dalam (inner frame) dan detail armor yang presisi.\r\n\r\nSkala & Kategori: 1/100 Scale (setara Master Grade / MG)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Inner Frame penuh yang kokoh\r\n\r\nDesain & Warna: Tampilan khas Gundam Vidar dengan proporsi tajam, aksen warna hitam, biru gelap, dan putih\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Burst Saber (pedang utama beserta unit sabuk peluru/bilah cadangan di bagian pinggang)\r\n\r\n2x Handgun (pistol ganda yang bisa disimpan di balik armor pinggang)\r\n\r\n1x Rifle (senapan serbu utama)\r\n\r\nFoot Blade (mekanisme pisau lipat di bagian telapak dan tumit kaki)\r\n\r\nWater Slide Decals & set tangan opsional\r\n\r\nStok ready. Silakan diorder.', 520000.00, 3, 'https://www.gundam.my/detailimage/interactive/10091/2.jpg', 'https://down-id.img.susercontent.com/file/id-11134207-822ws-mpz85m7w83k2b7.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wk-mpz8m7k43vuqf3.webp', 'https://down-id.img.susercontent.com/file/id-11134207-822wo-mpz85m7w9i4i66.webp', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTk36ZUwYOOUW09CFU0i-QGaR4swLqSqOnutC7eeX86dKEH6iSy-oiB_FS1&s=10', NULL, NULL, NULL, 0, 'Ready Stock', '2026-08-11 08:35:15', 'Bootleg'),
(48, 1, 'SNAA Divine invoker percival Deluxe ver BE 1/144', 'SNAA SC-009 DIVINE INVOKER PERCIVAL (1/144 BE SERIES)\r\n\r\nModel kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Mengusung konsep ksatria penyihir/penyeru (invoker) dengan desain jubah mekanis yang anggun, artikulasi fleksibel, serta presisi tinggi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Estetika ksatria taktis dengan ornamen jubah mekanis (binder/cape), part bening transparan, dan separasi warna presisi\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Staff / Spear (senjata tongkat/tombak sihir mekanis utama)\r\n\r\nRangkaian Floating Orbs / Sub-Unit Effect Parts\r\n\r\nSet tangan opsional untuk pose pemanggilan sihir atau pertempuran jarak dekat\r\n\r\nWater Slide Decals & stiker reflektif untuk detail bodi\r\n\r\nStok ready. Silakan diorder.', 350000.00, 7, 'https://www.gundam.my/images/sell_products/big/image_9756.jpg', 'https://www.gundam.my/images/sell_products/interactive/9756/5.jpg', 'https://www.gundam.my/images/sell_products/interactive/9756/1.jpg', 'https://www.gundam.my/images/sell_products/interactive/9756/6.jpg', 'https://www.gundam.my/images/sell_products/interactive/9756/9.jpg', NULL, NULL, NULL, 90, '', '2026-08-24 07:59:09', 'Mochin'),
(49, 1, 'SNAA Soul spear lamorak  BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Mengusung konsep ksatria penombak ulung dengan artikulasi dinamis, proporsi tajam, dan presisi tinggi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Tampilan armor ksatria taktis yang aerodinamis dengan separasi warna part presisi tanpa perlu dicat\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Soul Spear (tombak raksasa utama dengan opsi artikulasi/transformasi)\r\n\r\n1x Sub-Shield / Perisai Pelindung Taktis\r\n\r\nSet tangan opsional untuk berbagai pose aksi penyerangan\r\n\r\nWater Slide Decals & stiker reflektif untuk detail mata dan monitor\r\n\r\nStok ready. Silakan diorder.', 240000.00, 10, 'https://www.gundam.my/images/sell_products/big/image_9824.jpg', 'https://www.gundam.my/images/sell_products/interactive/9824/2.jpg', 'https://www.gundam.my/images/sell_products/interactive/9824/6.jpg', 'https://www.gundam.my/images/sell_products/interactive/9824/4.jpg', 'https://www.gundam.my/images/sell_products/interactive/9824/5.jpg', NULL, NULL, NULL, 0, '', '2026-08-24 08:04:06', 'Mochin'),
(50, 1, 'SNAA Furious warhammer gallahad  BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Mengusung konsep ksatria berat bersenjata palu perang raksasa dengan artikulasi fleksibel dan presisi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Tampilan armor ksatria berlapis yang tebal dan kokoh dengan separasi warna part tajam presisi\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Furious Warhammer (palu perang raksasa utama dengan daya hantam visual yang masif)\r\n\r\n1x Heavy Shield / Perisai Pelindung Taktis\r\n\r\nSet tangan opsional untuk memegang senjata dua tangan\r\n\r\nWater Slide Decals & stiker reflektif detail bodi\r\n\r\nStok ready. Silakan diorder.', 310000.00, 7, 'https://www.gundam.my/images/sell_products/big/image_10113.jpg', 'https://www.gundam.my/images/sell_products/interactive/10113/9.jpg', 'https://www.gundam.my/images/sell_products/interactive/10113/5.jpg', 'https://www.gundam.my/images/sell_products/interactive/10113/7.jpg', 'https://www.gundam.my/images/sell_products/interactive/10113/6.jpg', NULL, NULL, NULL, 0, '', '2026-08-24 08:06:34', 'Mochin'),
(51, 1, 'SNAA Rampage beast bedivere  BE 1/144', 'Model kit mecha berskala 1/144 dari lini Beyond Exquisite (BE) seri Knights of the Round Table buatan SNAA. Mengusung konsep ksatria bersenjata cakar beast dan pedang taktis dengan artikulasi sangat fleksibel serta presisi setara kasta Real Grade (RG).\r\n\r\nSkala & Seri: 1/144 Scale (Beyond Exquisite / BE Series)\r\n\r\nTinggi Figur: ±14 cm (standar proporsi 1/144)\r\n\r\nKonstruksi: Snap-fit plastic model kit dengan struktur Full Movable Inner Frame mandiri yang kokoh\r\n\r\nDesain & Warna: Siluet bodi agresif bernuansa beast tempur dengan separasi warna part presisi dan tajam\r\n\r\nPersenjataan & Aksesori:\r\n\r\n1x Beast Claw Set (cakar taktis tajam untuk pertarungan jarak dekat)\r\n\r\n1x Tactical Blade / Great Sword\r\n\r\nSet tangan opsional untuk berbagai pose pose pose liar & dinamis\r\n\r\nWater Slide Decals & stiker reflektif detail bodi\r\n\r\nStok ready. Silakan diorder.', 360000.00, 9, 'https://www.gundam.my/images/sell_products/big/image_10203.jpg', 'https://www.gundam.my/images/sell_products/interactive/10203/10.jpg', 'https://www.gundam.my/images/sell_products/interactive/10203/12.jpg', 'https://www.gundam.my/images/sell_products/interactive/10203/9.jpg', 'https://www.gundam.my/images/sell_products/interactive/10203/7.jpg', NULL, NULL, NULL, 0, '', '2026-08-24 08:09:28', 'Mochin');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `invoice_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `shipping_cost` decimal(12,2) DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voucher_id` int DEFAULT NULL,
  `voucher_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voucher_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `user_voucher_id` int DEFAULT NULL,
  `points_used` int NOT NULL DEFAULT '0',
  `points_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `points_earned` int NOT NULL DEFAULT '0',
  `status` enum('pending','paid','shipped','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `recipient_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `invoice_no`, `total`, `shipping_cost`, `grand_total`, `payment_method`, `shipping_method`, `voucher_id`, `voucher_code`, `voucher_discount`, `user_voucher_id`, `points_used`, `points_discount`, `points_earned`, `status`, `recipient_name`, `recipient_phone`, `shipping_address`, `created_at`) VALUES
(5, 1, 'INV-20260810-0001', 2850000.00, 24000.00, 2874000.00, 'COD (Cash on Delivery)', 'SiCepat REG', NULL, NULL, 0.00, NULL, 0, 0.00, 0, 'pending', 'Nikaidou', '081234567890', 'Jakarta, Indonesia', '2026-08-10 04:12:46'),
(6, 1, 'INV-20260907-0001', 88200.00, 25000.00, 112700.00, 'COD (Cash on Delivery)', 'JNE REG', NULL, NULL, 0.00, NULL, 500, 500.00, 1127, 'pending', 'Nikaidou', '081234567890', 'Jakarta, Indonesia', '2026-09-07 07:30:20'),
(7, 1, 'INV-20260907-0002', 520000.00, 25000.00, 495000.00, 'GoPay', 'JNE REG', 1, 'WELCOME10', 50000.00, 1, 0, 0.00, 4950, 'pending', 'Nikaidou', '081234567890', 'Jakarta, Indonesia', '2026-09-07 07:33:51'),
(8, 5, 'INV-20260910-0001', 33500.00, 25000.00, 58500.00, 'GoPay', 'JNE REG', NULL, NULL, 0.00, NULL, 0, 0.00, 585, 'pending', 'pelanggan siji', '082317761703', 'crb,talun', '2026-09-10 07:36:03');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int NOT NULL,
  `transaction_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`) VALUES
(9, 5, 7, 'PVC Figure 1/7 Gotoh Hitori - Live Ver. Bocchi the Rock!', 1, 2850000.00, 2850000.00),
(10, 6, 46, 'SNAA Titan great sword tristan BE 1/144', 1, 88200.00, 88200.00),
(11, 7, 47, 'MG Gundam vidar bootleg Tiger model', 1, 520000.00, 520000.00),
(12, 8, 5, 'Freedom Ascension [GD05] gundam card game', 1, 33500.00, 33500.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `points` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `avatar`, `role`, `points`, `created_at`) VALUES
(1, 'admin', 'admin@anohobby.id', '$2b$10$I3uAygp6.GAYzUMDdUDr2.Vatat9TVG0CTNv5mRrLFLdUZKe/59fi', 'Nikaidou', '081234567890', 'Jakarta, Indonesia', 'avatars/avatar_1_1786333035_e67fe7.jpg', 'admin', 6077, '2026-07-29 16:43:07'),
(4, 'Nikaidou', 'nikaido505@gmail.com', '$2y$10$iukzKPbKZEA87b7rnNWADe2PjBtIamlUMUzqf0N6caDZpbNLaps0C', 'Gotoh Eka', '080221051514', '', 'avatars/avatar_4_1786680022_2395d0.jpg', 'admin', 500, '2026-08-14 03:59:30'),
(5, 'pelanggan', 'pelanggan123@gmail.com', '$2y$10$jTiiWIV4Q6u94ZckS1ytAeZgX.Wcj5vu192X.8aWI00aFu5IDSR6m', 'pelanggan siji', '082317761703', 'crb,talun', NULL, 'customer', 1085, '2026-08-24 06:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `user_vouchers`
--

CREATE TABLE `user_vouchers` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `voucher_id` int NOT NULL,
  `voucher_code_snapshot` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(12,2) NOT NULL,
  `min_purchase` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_discount` decimal(12,2) DEFAULT NULL,
  `status` enum('available','used','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `claimed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `transaction_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_vouchers`
--

INSERT INTO `user_vouchers` (`id`, `user_id`, `voucher_id`, `voucher_code_snapshot`, `description_snapshot`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `status`, `claimed_at`, `expires_at`, `used_at`, `transaction_id`) VALUES
(1, 1, 1, 'WELCOME10', 'Diskon 10% untuk member baru, max Rp 50.000', 'percent', 10.00, 100000.00, 50000.00, 'used', '2026-09-07 06:58:19', '2026-10-07 06:58:19', '2026-09-07 14:33:51', 7);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `min_purchase` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_discount` decimal(12,2) DEFAULT NULL,
  `quota` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `claimable` tinyint(1) NOT NULL DEFAULT '0',
  `claim_quota` int DEFAULT NULL,
  `claimed_count` int NOT NULL DEFAULT '0',
  `claim_starts_at` datetime DEFAULT NULL,
  `claim_ends_at` datetime DEFAULT NULL,
  `voucher_validity_days` int NOT NULL DEFAULT '30',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `quota`, `used_count`, `starts_at`, `ends_at`, `is_active`, `claimable`, `claim_quota`, `claimed_count`, `claim_starts_at`, `claim_ends_at`, `voucher_validity_days`, `created_at`) VALUES
(1, 'WELCOME10', 'Diskon 10% untuk member baru, max Rp 50.000', 'percent', 10.00, 100000.00, 50000.00, 100, 1, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 1, 1, 100, 1, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 30, '2026-08-28 05:57:14'),
(2, 'HEMAT50K', 'Potongan Rp 50.000 min belanja Rp 500.000', 'fixed', 50000.00, 500000.00, NULL, 50, 0, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 1, 1, 100, 0, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 30, '2026-08-28 05:57:14'),
(3, 'ANOHOBBY20', 'Diskon 20% semua item, max Rp 100.000', 'percent', 20.00, 200000.00, 100000.00, 30, 0, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 1, 1, 100, 0, '2026-08-01 00:00:00', '2026-12-31 23:59:59', 30, '2026-08-28 05:57:14');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_redemptions`
--

CREATE TABLE `voucher_redemptions` (
  `id` int NOT NULL,
  `voucher_id` int NOT NULL,
  `transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL,
  `redeemed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voucher_redemptions`
--

INSERT INTO `voucher_redemptions` (`id`, `voucher_id`, `transaction_id`, `user_id`, `code`, `discount_amount`, `redeemed_at`) VALUES
(1, 1, 7, 1, 'WELCOME10', 50000.00, '2026-09-07 07:33:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voucher_redemptions`
--
ALTER TABLE `voucher_redemptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
