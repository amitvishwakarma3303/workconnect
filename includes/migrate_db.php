<?php
// includes/migrate_db.php
// Automatically migrates database to match the "Worker Job Portal" synopsis.

function runWorkerJobPortalMigration(PDO $pdo) {
    try {
        // 1. Ensure roles table has user and worker
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `roles` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `role_name` varchar(50) NOT NULL UNIQUE,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $roles = ['admin', 'user', 'worker'];
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_name = ?");
        $insertRole = $pdo->prepare("INSERT INTO roles (role_name) VALUES (?)");
        foreach ($roles as $r) {
            $checkStmt->execute([$r]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertRole->execute([$r]);
            }
        }

        // 2. Categories Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL UNIQUE,
                `icon` varchar(50) NOT NULL DEFAULT 'fa-tools',
                `description` text DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Seed default categories from the synopsis
        $defaultCategories = [
            ['Plumber', 'fa-faucet-drip', 'Water pipe fitting, leak repair, bathroom and sanitary installation.'],
            ['Electrician', 'fa-bolt', 'Wiring, electrical repairs, appliance installations, and switchboards.'],
            ['Carpenter', 'fa-hammer', 'Furniture crafting, door and window framing, woodwork repair.'],
            ['Painter', 'fa-paint-roller', 'Interior and exterior painting, wall finishing, and polishing.'],
            ['Construction Worker', 'fa-trowel-bricks', 'Masonry, bricklaying, cement work, and building construction.'],
            ['Technician', 'fa-wrench', 'Appliance servicing, AC, refrigerator, and machine maintenance.'],
            ['Cleaner', 'fa-broom', 'Home and commercial cleaning, deep sanitation, and housekeeping.'],
            ['Driver', 'fa-id-card', 'Personal, commercial, and transport driving services.'],
            ['Mechanic', 'fa-gears', 'Two-wheeler and four-wheeler vehicle repair and servicing.'],
            ['Gardener', 'fa-seedling', 'Lawn maintenance, planting, tree trimming, and garden care.'],
            ['Cook', 'fa-utensils', 'Daily home cooking, event catering, and food preparation.']
        ];

        $catCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
        $catInsert = $pdo->prepare("INSERT INTO categories (name, icon, description) VALUES (?, ?, ?)");
        foreach ($defaultCategories as $cat) {
            $catCheck->execute([$cat[0]]);
            if ($catCheck->fetchColumn() == 0) {
                $catInsert->execute([$cat[0], $cat[1], $cat[2]]);
            }
        }

        // 3. Worker Profiles Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `worker_profiles` (
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
                CONSTRAINT `fk_worker_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_worker_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Migrate any existing employee_profiles into worker_profiles if empty
        $cntWorker = $pdo->query("SELECT COUNT(*) FROM worker_profiles")->fetchColumn();
        if ($cntWorker == 0) {
            $hasEmployeeProfiles = $pdo->query("SHOW TABLES LIKE 'employee_profiles'")->fetchColumn();
            if ($hasEmployeeProfiles) {
                $stmt = $pdo->query("SELECT * FROM employee_profiles");
                $insertWk = $pdo->prepare("
                    INSERT INTO worker_profiles (user_id, full_name, skills, phone, bio, location, daily_rate)
                    VALUES (?, ?, ?, ?, ?, 'Mumbai', '800')
                    ON DUPLICATE KEY UPDATE skills = VALUES(skills)
                ");
                while ($row = $stmt->fetch()) {
                    $insertWk->execute([
                        $row['user_id'],
                        $row['full_name'] ?? null,
                        $row['skills'] ?? null,
                        $row['phone'] ?? null,
                        $row['bio'] ?? null
                    ]);
                }
            }
        }

        // 4. Work Requests Table (Person Who Needs a Worker -> Contact/Hire Worker)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `work_requests` (
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
                CONSTRAINT `fk_req_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_req_worker` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_req_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

    } catch (Exception $e) {
        // Silently log or handle during runtime without crashing
        error_log("Worker Job Portal migration notice: " . $e->getMessage());
    }
}
