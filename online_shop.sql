-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 01:14 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(16, 7, 12, 1, '2025-10-08 08:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'อุปกรณ์เกม'),
(4, 'อุปกรณ์คอมพิวเตอร์'),
(5, 'คอมพิวเตอร์ตั้งโต๊ะ'),
(6, 'โทรศัพท์มือถือ'),
(7, 'โน๊ตบุ๊ค'),
(8, 'แท็บเล็ต'),
(9, 'อุปกรณ์เสริมมือถือ'),
(12, 'ฮาร์ดแวร์คอมพิวเตอร์');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
(3, NULL, '22400.00', '2025-09-25 04:14:21', 'pending'),
(4, 7, '5200.00', '2025-10-08 08:25:22', 'pending'),
(5, 4, '45000.00', '2025-10-10 08:26:38', 'pending'),
(6, 4, '70900.00', '2025-10-10 09:24:09', 'completed'),
(7, NULL, '58750.00', '2025-10-10 09:58:39', 'pending'),
(8, 9, '29750.00', '2025-10-10 10:07:41', 'completed'),
(9, 10, '79000.00', '2025-10-10 10:15:29', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(7, 3, 9, 1, '2400.00'),
(9, 4, 12, 2, '2600.00'),
(10, 5, 4, 1, '45000.00'),
(11, 6, 15, 1, '25900.00'),
(12, 6, 4, 1, '45000.00'),
(13, 7, 18, 2, '29000.00'),
(14, 7, 16, 1, '750.00'),
(15, 8, 18, 1, '29000.00'),
(16, 8, 16, 1, '750.00'),
(17, 9, 18, 1, '29000.00'),
(18, 9, 17, 2, '25000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `stock`, `image`, `category_id`, `created_at`) VALUES
(4, 'Samsung Galaxy S25 Ultra (12+512GB) Titanium Black (5G)', 'Screen Size	6.90 inch\r\nChip	Snapdragon 8 Elite\r\nDisplay	QHD+ (3120 x 1440), Dynamic AMOLED 2X 1-120Hz\r\nMemory	RAM 12GB / ROM 512GB\r\nexpandable memory and sim card	Not Support/Dual SIM(1 single+1 single)\r\nOperating System	Android 15\r\nFront Camera	12MP\r\nBack Camera	200MP (Main) + 10MP (Telephoto) + 50MP (Periscope telephoto) + 50MP (Ultrawide)\r\nNetwork	2G, 3G, 4G, 5G\r\nwater resistant	Yes\r\nCharging	USB Type-C Wireless PowerShare, Super Fast Charging 45W, FWC 2.0 (Fast Wireless Charging 2.0 10W or more)\r\nBattery	5,000 mAh\r\nColor	Titanium Black\r\nDimensions W x D x H	7.76 x 0.82 x 16.28 cm.\r\nWeight	0.21 Kg.\r\nWarranty	1 Year\r\nOption	N/A', '45000.00', 4, 'product_1759905342.jpg', 6, '2025-09-18 01:45:46'),
(9, 'เมาส์ ASUS ROG GLADIUS III WIRELESS AIMPOINT', 'ASUS ROG GLADIUS III WIRELESS AIMPOINT (P711)\r\nUSB 2.0 (TypeC to TypeA)\r\nBluetooth 5.1\r\nRF 2.4GHz\r\n100~36000 DPI\r\n5 Buttons\r\nLi-ion battery\r\nรับประกันศูนย์ 2 ปี\r\nสินค้าใหม่ มือ 1', '2400.00', 5, 'product_1758701326.jpg', 4, '2025-09-18 05:00:06'),
(10, 'Apple iPhone 15 Plus 128GB Black', 'iPhone 15 Plus Dynamic Island มาอยู่บน iPhone 15 แล้วทำให้คุณไม่พลาดเรื่องราวต่าง ๆ เสริมด้วยกล้องหลัก 48 MP ถ่ายภาพที่มีความละเอียดสูงได้แบบง่าย ๆ ทั้งยังมีเทเลโฟโต้ 2 เท่าอีกด้วย และเปลี่ยนช่องเชื่อมต่อเป็น USB-C มาในดีไซน์แบบกระจกแต่งสีและอะลูมิเนียมที่ทนทาน\r\n\r\nการเชื่อมต่อด้วยช่องต่อ USB-C\r\nDynamic Island ให้คุณไม่พลาดเรื่องราวไหนๆ\r\nชิป A16 Bionic สุดทรงพลัง ชิปที่เร็วสุดแรงสุด', '29900.00', 3, 'product_1759905739.jpg', 6, '2025-10-08 06:42:19'),
(11, 'Acer Nitro Lite 16 NL16-71G-59HM Black', 'Screen Size	16.0 inch\r\nProcessor	Intel Core i5\r\nProcessor Speed	Intel Core i5-13420H, Up to 4.6GHz, 8C(4P+4E)/12T, 12MB Cache\r\nDisplay	IPS WUXGA (1920x1200) 165Hz\r\nMemory	16GB\r\nStorage	512GB SSD M.2\r\nGraphics	NVIDIA GeForce RTX 3050 Laptop\r\nOperating System	Windows 11 Home\r\nCamera	Acer webcam\r\nOptical Drive	No\r\nConnection ports	1 x USB Type-C Thunderbolt 4 , 3 x USB 3.2 port\r\nWireless	WiFi AX with 2x2 MU-MIMO Technology, Supports Bluetooth 5.1 or above\r\nBattery	3 Cell\r\nBattery Life	up to 3.5 hours\r\nColor	Shale Black\r\nDimensions W x D x H	36.3 x 24.2 x 2.3 cm.\r\nWeight	1.95 Kg.\r\nWarranty	3 Years (Parts & Labor & Onsite) Warranty\r\nOption	Keyboard TH/EN', '23990.00', 0, 'product_1759907821.jpg', 7, '2025-10-08 07:17:01'),
(12, 'จอมอนิเตอร์ VIEWSONIC VA240A-H (IPS 120Hz 1ms)', 'Screen Size	24.0 inch\r\nResolution	1920 x 1080 @ 120 Hz\r\nColor resolution	16.7 Millions\r\nBrightness	250 cd/m2\r\nContrast ratio	1500:1\r\nResponse Time	1 ms\r\nConnectors	1x VGA Port, 1x HDMI Port 1.4\r\nWidescreen	16:9\r\nWeight	6.6 Kg.\r\nColor	Black\r\nInput Video Compatibility	N/A\r\nD-Sub	1x VGA Port\r\nDVI	N/A\r\nHDMI	1x HDMI Port 1.4\r\nDisplay Port	N/A\r\nPanel Type	IPS\r\nPower Supply	100-240V, 50/60 Hz\r\nPower Consumption	22 W\r\nDimensions W x D x H	53.9 x 18.8 x 32.4 cm.\r\nWarranty	3 Years', '2600.00', 10, 'product_1759909803.jpg', 4, '2025-10-08 07:50:03'),
(13, 'ASUS DESKTOP AIO V440VAK-WPC072WA White', 'คอมพิวเตอร์ All in One PC จอเดียวใช้งานได้ครบทุกอย่าง ASUS V400 AiO V440VAK-WPC072WA หน้าจอความละเอียด Full HD (1920 x 1080) สีสันสมจริง ขนาด 24 นิ้ว เร็วแรง ทำงานลื่น ด้วยขุมพลัง Intel Core i7-13620H มองจากมุมไหนก็เห็นสีชัดไม่ผิดเพี้ยนด้วยเทคโนโลยี wide-view บนจอ widescreen ที่มุมมองกว้างถึง 178 องศา เชื่อมต่อไร้สายได้รวดเร็ว WiFi 6E และ Bluetooth 5.3 ตัวเครื่องมาพร้อมเมาส์และ คีย์บอร์ดมีสาย พร้อมใช้งานทันที\r\n\r\nCPU: Intel Core i7-13620H, Up to 4.9 GHz, 10 cores, 16 Threads\r\nGraphics: Intel UHD Graphics\r\nRAM: 16GB DDR5  \r\nSSD: 512GB M.2 SSD', '23990.00', 10, 'product_1760080089.jpg', 5, '2025-10-10 07:08:09'),
(14, 'Apple iPad Air 11-inch (M3) Wi-Fi + Cellular 128GB Purple (2025)', 'iPad Air รุ่น 11 นิ้ว อัดฉีดพลังแรงโดยชิป Apple M3 ที่เร็วสุดขั้ว  มาพร้อมจอภาพ Liquid Retina ที่สวยงามน่าทึ่ง, กล้องหน้า 12MP Center Stage เพื่อวิดีโอคอลที่ดียิ่งขึ้น ตลอดจน Wi-Fi 6E และ 5G  ที่เร็วสุดแรง ทั้งยังใช้งานร่วมกับ Apple Pencil Pro, Magic Keyboard และ Smart Folio ได้อีกด้วย คุณจึงสามารถทำสิ่งต่างๆ แบบมัลติทาสก์ เรียนรู้ ทำงาน เล่นสนุก และสร้างสรรค์จากที่ไหนก็ได้\r\n\r\nรองรับ Apple Pencil Pro\r\nรองรับ Apple Pencil (USB‑C)', '27900.00', 102, 'product_1760080168.jpg', 8, '2025-10-10 07:09:28'),
(15, 'Samsung Galaxy Tab S9 LTE (12+256GB) Graphite (5G)', 'Samsung Galaxy Tab S9  แท็บเล็ตเรือธงที่พร้อมยกระดับทุกประสบการณ์ของคุณสู่ขั้นกว่า ด้วย หน้าจอ Dynamic AMOLED 2X ขนาด 11 นิ้ว ที่ให้สีสันสดใส คมชัดสมจริง Galaxy Tab S รุ่นแรกที่ทนน้ำ กันฝุ่น และคุณภาพที่ทนทาน  เชื่อมต่ออินเทอร์เน็ตได้ทุกที่ทุกเวลา  พร้อมไปกับคุณได้สุดกว่าเดิม ซื้อสินค้าได้ที่ BaNANA   ในราคาที่คุ้มค่ากว่าใคร\r\n\r\nหน้าจอขนาด 11 inch \r\nชิป   Snapdragon 8 Gen 2 \r\nมาตรฐานการกันน้ำและฝุ่น IP68\r\nรองรับการชาร์จไว 45W', '25900.00', 30, 'product_1760080318.jpg', 8, '2025-10-10 07:11:58'),
(16, 'พาวเวอร์แบงค์ AUKEY 10,000mAh PB-N83S Black', 'พาวเวอร์แบงค์ AUKEY Basix Mini 10000mAh (PPS, FCP, SCP) 22.5W PD ขนาดเล็กกว่าบัตรเครดิต ขอบโค้งมน , พอร์ตชาร์จ 2 ช่อง รองรับชาร์จเร็ว PD 22.5W และ QC3.0, มีโหมดชาร์จเข้าแบตสำรองไปด้วย สามารถนำขึ้นเครื่องบินได้\r\n\r\nความจุ 10,000 mAh\r\nรองรับการชาร์จเร็ว 22.5 W\r\n ขนาดเล็ก พกพาสะดวก', '750.00', 100, 'product_1760086778.jpg', 9, '2025-10-10 08:59:38'),
(17, 'HP OmniBook 5 Flip 14-fp0054TU Silver', 'HP OmniBook 5 Flip ขนาด 14 นิ้ว แล็ปท็อป 2-in-1 ที่มอบความอิสระในการใช้งานอย่างเต็มที่ ด้วย บานพับ 360 องศา คุณสามารถปรับเปลี่ยนรูปแบบการใช้งานได้อย่างง่ายดาย ไม่ว่าจะเป็นแล็ปท็อป แท็บเล็ต หรือโหมดเต็นท์ เพื่อให้ได้ตำแหน่งที่ลงตัวที่สุดสำหรับการทำงานและความคิดสร้างสรรค์\r\n\r\nCPU: Intel Core i5-1334U, up to 4.6GHz, 10C(2P+8E)/12T, 12MB Cache\r\nGraphics: Intel Iris Xe Graphics\r\nRAM: 16GB LPDDR5\r\nSSD: 512GB  NVMe PCIe M.2', '25000.00', 15, 'product_1760087669.jpg', 7, '2025-10-10 09:14:29'),
(18, 'Sony PlayStation 5 Pro', 'เครื่องเล่นเกม Sony PlayStation 5 Pro โฉมใหม่ ที่ BaNANA ได้รับการออกแบบให้ใช้งานได้ยืดหยุ่นมากขึ้น ด้วยขนาดที่เล็กลงและน้ำหนักเบาลง พร้อมการอัปเกรดครั้งยิ่งใหญ่สู่รายละเอียดความคมชัดระดับ 4K สีสันสดใสสมจริง และพื้นที่จัดเก็บ SSD 2TB ที่ช่วยให้คุณรวมเกมได้มากยิ่งขึ้น โดยในรุ่นนี้คอนโซลจะมาในรูปแบบดิจิทัลทั้งหมดโดยไม่มีไดรฟ์ดิสก์\r\n\r\nเพืิ่มความสมจริงของแสงเงาด้วย   Ray Tracing\r\nพื้นที่จัดเก็บขนาด 2TB \r\nความละเอียดคมชัดสูงสุด 4K 60fps และ 120 Hz', '29000.00', 4, 'product_1760087807.jpg', 1, '2025-10-10 09:16:47'),
(21, 'ADATA 512GB SU650 SSD 3D Nand SATA 2.5', 'ความจุ: 512 GB ขนาด SSD SATA 2.5-inch\r\nความเร็วในการอ่าน/เขียน: Read: 520 MB/s | Write: 450 MB/s\r\nอินเตอร์เฟส: SATA III\r\nราคาย่อมเยาย์', '1200.00', 7, 'product_1760092830.jpg', 12, '2025-10-10 10:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT 'Rating from 1 to 5',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 11, 7, 5, 'ใช้ดีมากๆๆๆ ต้องบอกต่อ', '2025-10-08 07:42:58'),
(3, 16, 9, 4, 'ใช้ได้ดี ทนทาน ขนาดเล็ก เบา', '2025-10-10 10:10:12'),
(4, 16, 10, 5, 'ราคาไม่แรง ขนาดเล็กกำลังดี', '2025-10-10 10:16:31');

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `shipping_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `shipping_status` enum('not_shipped','shipped','delivered') DEFAULT 'not_shipped'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping`
--

INSERT INTO `shipping` (`shipping_id`, `order_id`, `address`, `city`, `postal_code`, `phone`, `shipping_status`) VALUES
(3, 3, '123', 'Ratchaburi', '70130', '085468795', 'not_shipped'),
(4, 4, '257/6', 'ดำเนิน', '70100', '099123456', 'not_shipped'),
(5, 5, '257 หมู่ 3 ตำบลดอนกรวย อำเภอดำเนินสะดวก', 'ไทย', '70130', '0999999999', 'not_shipped'),
(6, 6, '257 หมู่ 3 ตำบลดอนกรวย อำเภอดำเนินสะดวก', 'ไทย', '70130', '0999999999', 'delivered'),
(7, 7, '123 / 3 ดอรกรวย ดำเนินสะดวก', 'ราชบุรี', '70130', '099123456', 'not_shipped'),
(8, 8, '123 / 3 ดอรกรวย ดำเนินสะดวก', 'ราชบุรี', '70130', '0999999999', 'delivered'),
(9, 9, '123 / 3 ดอรกรวย ดำเนินสะดวก', 'ราชบุรี', '70130', '0999999999', 'delivered');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin1', 'admin_pass', 'admin1@example.com', 'Admin One', 'admin', '2025-09-18 01:29:04'),
(4, 'admin', '$2y$10$H95535Bg5IPMDi1LbUdBteJINblXt6GCLbGlvb2QZk8YRfFWexU2G', 'bb@email.com', 'adisak Yongpanya', 'admin', '2025-09-18 01:30:02'),
(6, 'Jonh123', '$2y$10$mO5v9lDRwqNyqCs9EzpWIOsPyDY9QQRBC8zGR6jDG5GCNbhJFBibG', 'Jonhmax123@email.com', 'Jonh Max', 'member', '2025-10-06 12:34:49'),
(7, 'Boboniisan', '$2y$10$8scRMIgQNhVTEKDW3wUVxel/Rui74/L4t61DZ843b96euEcCzquRO', 'bbnn@mail.com', 'Bobo Nisson', 'member', '2025-10-08 07:39:51'),
(9, 'Turbo123', '$2y$10$3slYckT9nTOJmqNudurFmOFJ8Pg7cSwhY9m4.Z7SnXzojzJe/b6De', 'bobo123@email.com', 'Adisak Yongpanya', 'member', '2025-10-10 10:04:10'),
(10, 'bobo', '$2y$10$cqaoTFnDFoXZq5BHRVILduGEwyrZs8te2DPdNLm5eiOfFFtsXLbx.', 'bb567@email.com', 'Adisak Yongdee', 'member', '2025-10-10 10:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`, `created_at`) VALUES
(21, 4, 9, '2025-10-08 07:27:21'),
(22, 4, 10, '2025-10-08 07:27:51'),
(23, 7, 11, '2025-10-08 07:40:24'),
(28, 9, 18, '2025-10-10 10:06:09'),
(29, 9, 17, '2025-10-10 10:08:30'),
(30, 10, 18, '2025-10-10 10:14:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `product_user_unique` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping`
--
ALTER TABLE `shipping`
  ADD CONSTRAINT `shipping_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
