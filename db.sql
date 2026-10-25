-- WorkConnect - Worker Job Portal Database Dump
-- Generated on 2026-10-25 14:09:54
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `roles`
INSERT INTO `roles` (`id`, `role_name`) VALUES
('1', 'admin'),
('4', 'user'),
('5', 'worker');

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-tools',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `icon`, `description`, `created_at`) VALUES
('1', 'Plumber', 'fa-faucet-drip', 'Water pipe fitting, leak repair, bathroom and sanitary installation.', '2026-10-25 17:50:19'),
('2', 'Electrician', 'fa-bolt', 'Wiring, electrical repairs, appliance installations, and switchboards.', '2026-10-25 17:50:19'),
('3', 'Carpenter', 'fa-hammer', 'Furniture crafting, door and window framing, woodwork repair.', '2026-10-25 17:50:19'),
('4', 'Painter', 'fa-paint-roller', 'Interior and exterior painting, wall finishing, and polishing.', '2026-10-25 17:50:19'),
('5', 'Construction Worker', 'fa-trowel-bricks', 'Masonry, bricklaying, cement work, and building construction.', '2026-10-25 17:50:19'),
('6', 'Technician', 'fa-wrench', 'Appliance servicing, AC, refrigerator, and machine maintenance.', '2026-10-25 17:50:19'),
('7', 'Cleaner', 'fa-broom', 'Home and commercial cleaning, deep sanitation, and housekeeping.', '2026-10-25 17:50:19'),
('8', 'Driver', 'fa-id-card', 'Personal, commercial, and transport driving services.', '2026-10-25 17:50:19'),
('9', 'Mechanic', 'fa-gears', 'Two-wheeler and four-wheeler vehicle repair and servicing.', '2026-10-25 17:50:19'),
('10', 'Gardener', 'fa-seedling', 'Lawn maintenance, planting, tree trimming, and garden care.', '2026-10-25 17:50:19'),
('11', 'Cook', 'fa-utensils', 'Daily home cooking, event catering, and food preparation.', '2026-10-25 17:50:19');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `created_at`) VALUES
('4', 'admin', 'admin@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '1', '2026-10-02 20:45:57'),
('5', 'amit', 'amitvishwakarma3303@gmail.com', '$2y$10$GB1V6TwNyx6eFHakIrG9K.GQh1kt79pXlSbfjAyQZDaOJ800h1Qxa', '4', '2026-10-25 17:51:22'),
('6', 'rohit_sharma', 'rohit.client@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '4', '2026-10-25 17:54:40'),
('7', 'priya_patel', 'priya.patel@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '4', '2026-10-25 17:54:40'),
('8', 'anita_deshmukh', 'anita.deshmukh@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '4', '2026-10-25 17:54:40'),
('9', 'vikram_singh', 'vikram.singh@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '4', '2026-10-25 17:54:40'),
('10', 'sunil_plumber', 'sunil.plumber@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('11', 'rajesh_electrician', 'rajesh.electrician@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('12', 'amit_carpenter', 'amit.carpenter@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('13', 'mahesh_painter', 'mahesh.painter@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('14', 'ramesh_builder', 'ramesh.mason@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('15', 'vijay_technician', 'vijay.tech@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('16', 'deepak_cleaner', 'deepak.cleaner@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('17', 'sanjay_driver', 'sanjay.driver@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('18', 'arun_mechanic', 'arun.mechanic@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('19', 'karan_gardener', 'karan.gardener@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:40'),
('20', 'geeta_cook', 'geeta.cook@gmail.com', '$2y$10$EX6Gxz728eUhr0NecNc4N.WkDvjqxnBl1kiG2v85snMiujaCC5aTy', '5', '2026-10-25 17:54:41');

-- --------------------------------------------------------
-- Table structure for table `worker_profiles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `worker_profiles`;
CREATE TABLE `worker_profiles` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience_years` int(11) NOT NULL DEFAULT 1,
  `experience_details` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `availability` enum('available','busy','unavailable') NOT NULL DEFAULT 'available',
  `daily_rate` varchar(100) DEFAULT NULL,
  `hourly_rate` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_worker_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_worker_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `worker_profiles`
INSERT INTO `worker_profiles` (`user_id`, `full_name`, `category_id`, `skills`, `experience_years`, `experience_details`, `location`, `availability`, `daily_rate`, `hourly_rate`, `phone`, `bio`, `rating`, `created_at`) VALUES
('10', 'Sunil Vishwakarma', '1', 'Pipe fitting, Leak repair, Tap & valve replacement, Water tank cleaning, PVC joints, Geyser connection', '8', '8 years of specialized residential and commercial plumbing experience. Repaired over 350+ household leakage issues across Nallasopara and Vasai.', 'Nallasopara (East), Mumbai', 'available', '800', '150', '+91 98201 12345', 'Licensed and reliable master plumber equipped with full modern tooling. Available for emergency repairs and full house sanitary installations.', '4.90', '2026-10-25 17:54:40'),
('11', 'Rajesh Kumar Gupta', '2', 'House wiring, Inverter installation, MCB & fuse repair, Switchboard assembly, Ceiling fan fixing, LED lighting', '6', '6 years providing electrical wiring and troubleshooting for flats, shops, and offices. Certified in high-voltage safety.', 'Vasai (West), Mumbai', 'available', '900', '180', '+91 98334 56789', 'Experienced electrician specialized in quick fault detection, inverter setups, and safe concealed wiring for new renovations.', '4.85', '2026-10-25 17:54:40'),
('12', 'Amit Sutar', '3', 'Custom furniture, Door framing, Window mesh, Wooden wardrobe, Kitchen modular cabinets, Lock fitting', '10', 'Over a decade of craftsmanship working with plywood, teak wood, and laminate finishing. Specialized in modern space-saving furniture.', 'Virar (West), Mumbai', 'available', '1200', '220', '+91 97654 32109', 'Precision woodwork specialist. From minor door lock fixes to complete modular kitchen setups with quality guarantees.', '4.95', '2026-10-25 17:54:40'),
('13', 'Mahesh Chaurasia', '4', 'Interior emulsion, Exterior weathercoat, Wall putty, Texture designs, Enamel painting on doors, Waterproofing', '5', 'Handled painting projects for over 60+ residential apartments in Naigaon and Mira Road. Expert in surface prep and stain removal.', 'Naigaon (East), Mumbai', 'busy', '750', '120', '+91 98920 44556', 'Professional wall painter. Clean, neat work with minimal mess and high attention to color blending and wall smoothness.', '4.75', '2026-10-25 17:54:40'),
('14', 'Ramesh Yadav', '5', 'Masonry, Brickwork, Plastering, Concrete mixing, Tile fitting, Demolition and structural repair', '7', '7 years working on residential and commercial civil works. Experienced in floor tiling, wall plastering, and RCC labor.', 'Nallasopara (West), Mumbai', 'available', '700', '100', '+91 98199 88776', 'Hardworking and punctual civil construction worker. Expert in tile alignment, waterproofing plaster, and structural repairs.', '4.80', '2026-10-25 17:54:40'),
('15', 'Vijay Panchal', '6', 'Air Conditioner servicing, Gas refilling, Refrigerator cooling repair, Washing machine motor, Microwave servicing', '4', '4 years certified appliance technician. Specializes in split AC installation, deep chemical jet wash, and compressor repairs.', 'Borivali (East), Mumbai', 'available', '850', '200', '+91 99302 33445', 'Appliance repair technician with multi-brand expertise (LG, Samsung, Daikin, Voltas). Quick diagnosis with honest estimates.', '4.90', '2026-10-25 17:54:40'),
('16', 'Deepak Shinde', '7', 'Deep home cleaning, Bathroom descaling, Kitchen degreasing, Sofa shampooing, Floor scrubbing, Window glass wiping', '3', '3 years professional cleaning experience for move-in/move-out houses, offices, and post-renovation sites.', 'Andheri (West), Mumbai', 'available', '600', '100', '+91 98451 22334', 'Eco-friendly and thorough cleaner. Uses high-grade sanitizing equipment for deep bathroom, kitchen, and upholstery restoration.', '4.70', '2026-10-25 17:54:40'),
('17', 'Sanjay Patil', '8', 'Manual & Automatic transmission, Highway & Ghat driving, Commercial pickup, Outstation trips, Vehicle maintenance', '9', '9 years clean commercial driving record. Extensive knowledge of Maharashtra highways, Mumbai city shortcuts, and night driving.', 'Thane (West), Mumbai', 'available', '800', '120', '+91 97731 55667', 'Punctual, non-smoker, professional driver with valid commercial badge. Safe driving guaranteed for family or business travel.', '4.92', '2026-10-25 17:54:40'),
('18', 'Arun Auto Works', '9', 'Two-wheeler engine tune-up, Disc brake repair, Carburetor cleaning, Clutch overhaul, Battery & wiring testing', '6', '6 years running vehicle repairs and on-road breakdown assistance. Expert in Honda, Hero, Bajaj, and TVS bikes/scooters.', 'Vasai (East), Mumbai', 'available', '700', '150', '+91 98670 99881', 'On-call bike mechanic. Provides doorstep two-wheeler servicing, puncture repair, and emergency starting assistance.', '4.88', '2026-10-25 17:54:40'),
('19', 'Karan Mali', '10', 'Lawn mowing, Hedge trimming, Soil fertilizing, Plant repotting, Terrace gardening, Pest spraying', '5', '5 years landscaping residential societies and private bungalows in Vasai-Virar. Expert in flowering plants and seasonal pruning.', 'Naigaon (West), Mumbai', 'available', '600', '100', '+91 98112 33441', 'Passionate gardener helping transform balconies, terraces, and lawns into lush green sanctuaries with organic care.', '4.82', '2026-10-25 17:54:40'),
('20', 'Geeta Devi', '11', 'North Indian thali, Gujarati vegetarian, Maharashtrian cuisine, Roti & Paratha, Healthy breakfast, Party snacks', '8', '8 years catering to families and small celebrations. High standards of kitchen hygiene, prompt timings, and customized spice levels.', 'Nallasopara (East), Mumbai', 'busy', '650', '120', '+91 99200 44332', 'Experienced home cook. Prepares fresh, nutritious, and delicious daily home meals for working professionals and families.', '4.90', '2026-10-25 17:54:41');

-- --------------------------------------------------------
-- Table structure for table `work_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `work_requests`;
CREATE TABLE `work_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `work_date` date DEFAULT NULL,
  `budget` varchar(100) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `worker_id` (`worker_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_req_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_req_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_req_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `work_requests`
INSERT INTO `work_requests` (`id`, `user_id`, `worker_id`, `category_id`, `title`, `description`, `location`, `work_date`, `budget`, `status`, `created_at`) VALUES
('1', '6', '10', '1', 'Urgent bathroom pipeline leakage repair', 'Water is constantly dripping from the main cold water pipe behind the bathroom basin. Need someone to inspect and seal with fresh Teflon/PVC fitting today.', 'Flat 302, Green Heights, Nallasopara East', '2026-10-26', '800', 'pending', '2026-10-25 17:54:41'),
('2', '7', '11', '2', 'Main MCB tripping & ceiling fan regulator replacement', 'Whenever we turn on the AC, the hall MCB trips. Also need a new 5-step fan regulator installed in bedroom.', 'B-14 Shanti Niwas, Station Road, Vasai West', '2026-10-24', '900', 'accepted', '2026-10-25 17:54:41'),
('3', '8', '12', '3', 'Main door Godrej lock replacement and shoe rack shelf repair', 'Need old rim lock replaced with a new Godrej mortise lock. Also one shelf of wooden shoe rack has come loose and needs reinforcement.', '401 Sun Tower, Yashwant Nagar, Virar West', '2026-10-21', '1200', 'completed', '2026-10-25 17:54:41'),
('4', '9', '13', '4', 'Two coats paint for living room and dining area', 'Need Royale luxury emulsion applied on living room walls (approx 350 sq ft). Putty touchups needed on corners.', 'Flat 201, Evershine City, Naigaon East', '2026-10-18', '3500', 'completed', '2026-10-25 17:54:41'),
('5', '6', '15', '6', 'Split AC servicing and gas pressure check', 'Daikin 1.5 ton split AC not cooling effectively. Need indoor coil chemical wash and outdoor unit check.', 'Sector 4, Charkop, Borivali East', '2026-10-23', '850', 'rejected', '2026-10-25 17:54:41'),
('6', '5', '12', '3', 'repair door', 'awsghdjg.kl/knbkjmhnb b', 'Virar (West), Mumbai', '2026-10-26', '1000', 'completed', '2026-10-25 17:59:32');

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `messages`
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
('1', '2', '1', 'hii', '0', '2026-02-27 18:17:13'),
('2', '2', '1', 'hey', '0', '2026-02-27 18:17:26'),
('3', '2', '1', 'i have work for you', '0', '2026-02-27 18:19:06'),
('4', '1', '2', 'price ?', '0', '2026-02-27 18:19:17'),
('6', '2', '1', '150rs dega', '0', '2026-02-28 11:17:21'),
('7', '7', '11', 'Hello Rajesh ji, can you come around 4 PM tomorrow to check the MCB problem?', '1', '2026-10-25 17:54:41'),
('8', '11', '7', 'Namaste madam. Yes, 4 PM is fine. Please keep the new fan regulator ready, I will bring testing meter and wiring.', '1', '2026-10-25 17:54:41'),
('9', '7', '11', 'Perfect, thank you! Location is shared in the request.', '1', '2026-10-25 17:54:41'),
('10', '8', '12', 'Hi Amit, thanks for fixing the door lock so cleanly yesterday!', '1', '2026-10-25 17:54:41'),
('11', '12', '8', 'You are welcome maam! Glad you liked the finish. Reach out anytime if you need more woodwork done.', '1', '2026-10-25 17:54:41'),
('12', '5', '12', 'hii', '0', '2026-10-25 18:16:03'),
('13', '12', '5', 'hello', '0', '2026-10-25 18:16:12');

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `notifications`
INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
('1', '2', 'New application received from Sunil for your job posting: Carpenter', 'application', '1', '2026-02-28 11:01:43'),
('2', '10', 'New Work Request received from Rohit Sharma: Urgent bathroom pipeline leakage repair (Nallasopara)', 'application', '0', '2026-10-25 17:54:41'),
('3', '7', 'Good news! Rajesh Kumar Gupta accepted your work request for: Main MCB tripping & ceiling fan regulator replacement', 'hiring', '0', '2026-10-25 17:54:41'),
('4', '10', 'New Work Request received from Rohit Sharma: Urgent bathroom pipeline leakage repair (Nallasopara)', 'application', '0', '2026-10-25 17:54:41'),
('5', '7', 'Good news! Rajesh Kumar Gupta accepted your work request for: Main MCB tripping & ceiling fan regulator replacement', 'hiring', '0', '2026-10-25 17:54:41'),
('6', '12', 'New Work Request received from amit: repair door (Virar (West), Mumbai)', 'application', '1', '2026-10-25 17:59:32'),
('7', '5', 'Good news! amit_carpenter accepted your work request for: repair door', 'hiring', '1', '2026-10-25 18:14:52'),
('8', '5', 'amit_carpenter has marked your task \'repair door\' as completed.', 'hiring', '1', '2026-10-25 18:16:59');

-- --------------------------------------------------------
-- Table structure for table `contact_forms`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_forms`;
CREATE TABLE `contact_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `city` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `source_page` enum('home','contact') NOT NULL DEFAULT 'contact',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
