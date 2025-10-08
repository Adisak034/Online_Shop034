-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 11:12 AM
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
(12, 4, 4, 5, '2025-10-08 07:35:29'),
(14, 4, 12, 1, '2025-10-08 07:50:20'),
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
(1, 'อุปกรณ์เสริมไอที'),
(4, 'อุปกรณ์คอมพิวเตอร์'),
(5, 'คอมพิวเตอร์ตั้งโต๊ะ'),
(6, 'โทรศัพท์มือถือ'),
(7, 'โน๊ตบุ๊ค'),
(8, 'แท็บเล็ต');

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
(1, NULL, '834.00', '2025-09-18 01:29:04', 'processing'),
(2, NULL, '117200.00', '2025-09-25 03:59:07', 'pending'),
(3, NULL, '22400.00', '2025-09-25 04:14:21', 'pending'),
(4, 7, '5200.00', '2025-10-08 08:25:22', 'pending');

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
(4, 2, 4, 2, '45000.00'),
(5, 2, 9, 3, '2400.00'),
(7, 3, 9, 1, '2400.00'),
(9, 4, 12, 2, '2600.00');

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
(4, 'Samsung Galaxy S25 Ultra (12+512GB) Titanium Black (5G)', 'Screen Size	6.90 inch\r\nChip	Snapdragon 8 Elite\r\nDisplay	QHD+ (3120 x 1440), Dynamic AMOLED 2X 1-120Hz\r\nMemory	RAM 12GB / ROM 512GB\r\nexpandable memory and sim card	Not Support/Dual SIM(1 single+1 single)\r\nOperating System	Android 15\r\nFront Camera	12MP\r\nBack Camera	200MP (Main) + 10MP (Telephoto) + 50MP (Periscope telephoto) + 50MP (Ultrawide)\r\nNetwork	2G, 3G, 4G, 5G\r\nwater resistant	Yes\r\nCharging	USB Type-C Wireless PowerShare, Super Fast Charging 45W, FWC 2.0 (Fast Wireless Charging 2.0 10W or more)\r\nBattery	5,000 mAh\r\nColor	Titanium Black\r\nDimensions W x D x H	7.76 x 0.82 x 16.28 cm.\r\nWeight	0.21 Kg.\r\nWarranty	1 Year\r\nOption	N/A', '45000.00', 20, 'product_1759905342.jpg', 6, '2025-09-18 01:45:46'),
(9, 'เมาส์ ASUS ROG GLADIUS III WIRELESS AIMPOINT', 'ASUS ROG GLADIUS III WIRELESS AIMPOINT (P711)\r\nUSB 2.0 (TypeC to TypeA)\r\nBluetooth 5.1\r\nRF 2.4GHz\r\n100~36000 DPI\r\n5 Buttons\r\nLi-ion battery\r\nรับประกันศูนย์ 2 ปี\r\nสินค้าใหม่ มือ 1', '2400.00', 99, 'product_1758701326.jpg', 4, '2025-09-18 05:00:06'),
(10, 'Apple iPhone 15 Plus 128GB Black', 'iPhone 15 Plus Dynamic Island มาอยู่บน iPhone 15 แล้วทำให้คุณไม่พลาดเรื่องราวต่าง ๆ เสริมด้วยกล้องหลัก 48 MP ถ่ายภาพที่มีความละเอียดสูงได้แบบง่าย ๆ ทั้งยังมีเทเลโฟโต้ 2 เท่าอีกด้วย และเปลี่ยนช่องเชื่อมต่อเป็น USB-C มาในดีไซน์แบบกระจกแต่งสีและอะลูมิเนียมที่ทนทาน\r\n\r\nการเชื่อมต่อด้วยช่องต่อ USB-C\r\nDynamic Island ให้คุณไม่พลาดเรื่องราวไหนๆ\r\nชิป A16 Bionic สุดทรงพลัง ชิปที่เร็วสุดแรงสุด', '29900.00', 30, 'product_1759905739.jpg', 6, '2025-10-08 06:42:19'),
(11, 'Acer Nitro Lite 16 NL16-71G-59HM Black', 'Screen Size	16.0 inch\r\nProcessor	Intel Core i5\r\nProcessor Speed	Intel Core i5-13420H, Up to 4.6GHz, 8C(4P+4E)/12T, 12MB Cache\r\nDisplay	IPS WUXGA (1920x1200) 165Hz\r\nMemory	16GB\r\nStorage	512GB SSD M.2\r\nGraphics	NVIDIA GeForce RTX 3050 Laptop\r\nOperating System	Windows 11 Home\r\nCamera	Acer webcam\r\nOptical Drive	No\r\nConnection ports	1 x USB Type-C Thunderbolt 4 , 3 x USB 3.2 port\r\nWireless	WiFi AX with 2x2 MU-MIMO Technology, Supports Bluetooth 5.1 or above\r\nBattery	3 Cell\r\nBattery Life	up to 3.5 hours\r\nColor	Shale Black\r\nDimensions W x D x H	36.3 x 24.2 x 2.3 cm.\r\nWeight	1.95 Kg.\r\nWarranty	3 Years (Parts & Labor & Onsite) Warranty\r\nOption	Keyboard TH/EN', '23990.00', 0, 'product_1759907821.jpg', 7, '2025-10-08 07:17:01'),
(12, 'จอมอนิเตอร์ VIEWSONIC VA240A-H (IPS 120Hz 1ms)', 'Screen Size	24.0 inch\r\nResolution	1920 x 1080 @ 120 Hz\r\nColor resolution	16.7 Millions\r\nBrightness	250 cd/m2\r\nContrast ratio	1500:1\r\nResponse Time	1 ms\r\nConnectors	1x VGA Port, 1x HDMI Port 1.4\r\nWidescreen	16:9\r\nWeight	6.6 Kg.\r\nColor	Black\r\nInput Video Compatibility	N/A\r\nD-Sub	1x VGA Port\r\nDVI	N/A\r\nHDMI	1x HDMI Port 1.4\r\nDisplay Port	N/A\r\nPanel Type	IPS\r\nPower Supply	100-240V, 50/60 Hz\r\nPower Consumption	22 W\r\nDimensions W x D x H	53.9 x 18.8 x 32.4 cm.\r\nWarranty	3 Years', '2600.00', 10, 'product_1759909803.jpg', 4, '2025-10-08 07:50:03');

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
(1, 11, 7, 5, 'ใช้ดีมากๆๆๆ ต้องบอกต่อ', '2025-10-08 07:42:58');

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
(1, 1, '123 ถนนหลัก เขตเมือง', 'กรุงเทพมหานคร', '10100', '0812345678', 'shipped'),
(2, 2, 'ewfsdf', 'dsfdsf', '21347', '31313456764', 'not_shipped'),
(3, 3, '123', 'Ratchaburi', '70130', '085468795', 'not_shipped'),
(4, 4, '257/6', 'ดำเนิน', '70100', '099123456', 'not_shipped');

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
(4, 'admin', '$2y$10$H95535Bg5IPMDi1LbUdBteJINblXt6GCLbGlvb2QZk8YRfFWexU2G', 'bb@email.com', 'adisak', 'admin', '2025-09-18 01:30:02'),
(6, 'Jonh', '$2y$10$mO5v9lDRwqNyqCs9EzpWIOsPyDY9QQRBC8zGR6jDG5GCNbhJFBibG', 'Jonhmax@email.com', 'Jonh Max', 'member', '2025-10-06 12:34:49'),
(7, 'Boboniisan', '$2y$10$8scRMIgQNhVTEKDW3wUVxel/Rui74/L4t61DZ843b96euEcCzquRO', 'bbnn@mail.com', 'Bobo Nisson', 'member', '2025-10-08 07:39:51');

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
(23, 7, 11, '2025-10-08 07:40:24');

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
