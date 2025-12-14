SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `laundry_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `laundry_items` (`id`, `name`, `price`) VALUES
(5, 'Pant', 150.00),
(6, 'Shirt', 100.00),
(7, 'Saree', 200.00),
(8, 'Punjabi', 75.00);

CREATE TABLE `laundry_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `laundry_locations` (`id`, `name`, `address`, `phone`) VALUES
(4, 'Asha Laundry House', 'Uttara Sector - 5, Dhaka', '01914681243');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('Customer','Rider','Admin') NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `role`, `name`, `phone`, `address`, `email`, `password`, `approved`) VALUES
(1, 'Admin', 'Hamim', '01560000654', 'Uttara, Dhaka', 'hamim.ahmed541@gmail.com', 'HASHED_PASSWORD', 1),
(2, 'Customer', 'Rafi', '01780900234', 'Mirpur, Dhaka', 'rafi123@gmail.com', 'HASHED_PASSWORD', 1),
(3, 'Rider', 'Rakib', '01301234566', 'Basundhora, Dhaka', 'rakib123@gmail.com', 'HASHED_PASSWORD', 1),
(4, 'Rider', 'Abdul', '01714567890', 'Mirpur, Dhaka', 'abdul123@gmail.com', 'HASHED_PASSWORD', 1),
(5, 'Customer', 'Mehrab', '01568901285', 'Pallabi, Mirpur', 'mehrab123@gmail.com', 'HASHED_PASSWORD', 0);

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `laundry_location_id` int(11) NOT NULL,
  `status` enum('Pending','Picked Up','In Process','Completed','Delivered') DEFAULT 'Pending',
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `orders` (`id`, `customer_id`, `laundry_location_id`, `status`, `total_price`, `order_date`, `deadline`) VALUES
(1, 2, 4, 'Delivered', 400.00, '2025-03-08 09:53:48', '2025-03-10 18:00:00'),
(2, 2, 4, 'Delivered', 200.00, '2025-03-08 09:53:48', '2025-03-11 15:00:00'),
(3, 2, 4, 'Pending', 400.00, '2025-03-23 18:00:00', '0000-00-00 00:00:00');

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `laundry_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `order_items` (`id`, `order_id`, `laundry_item_id`, `quantity`, `price`) VALUES
(1, 1, 5, 2, 150.00),
(2, 1, 6, 1, 100.00),
(3, 2, 7, 1, 200.00),
(10, 3, 6, 2, 100.00),
(11, 3, 7, 1, 200.00);

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Online') NOT NULL,
  `payment_status` enum('Pending','Completed') DEFAULT 'Pending',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payments` (`id`, `order_id`, `customer_id`, `amount`, `payment_method`, `payment_status`) VALUES
(1, 1, 2, 400.00, 'Cash', 'Completed'),
(2, 2, 2, 200.00, 'Card', 'Pending'),
(7, 3, 2, 400.00, 'Online', 'Completed');

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11),
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reviews` (`id`, `order_id`, `customer_id`, `rating`, `comment`) VALUES
(1, 1, 2, 5, 'Excellent service!'),
(2, 2, 2, 4, 'Good service'),
(7, 3, 2, 4, 'Easy to use');

CREATE TABLE `rider_assignments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rider_id` int(11) NOT NULL,
  `status` enum('Assigned','Picked Up','Delivered') DEFAULT 'Assigned',
  `pickup_time` datetime,
  `delivery_time` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rider_assignments` (`id`, `order_id`, `rider_id`, `status`) VALUES
(3, 1, 3, 'Delivered'),
(4, 2, 3, 'Picked Up'),
(5, 3, 4, 'Picked Up');

ALTER TABLE `laundry_items` ADD PRIMARY KEY (`id`);
ALTER TABLE `laundry_locations` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `orders` ADD PRIMARY KEY (`id`), ADD KEY `customer_id` (`customer_id`), ADD KEY `laundry_location_id` (`laundry_location_id`);
ALTER TABLE `order_items` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`order_id`), ADD KEY `laundry_item_id` (`laundry_item_id`);
ALTER TABLE `payments` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`order_id`), ADD KEY `customer_id` (`customer_id`);
ALTER TABLE `reviews` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`order_id`), ADD KEY `customer_id` (`customer_id`);
ALTER TABLE `rider_assignments` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`order_id`), ADD KEY `rider_id` (`rider_id`);

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_fk1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_fk2` FOREIGN KEY (`laundry_location_id`) REFERENCES `laundry_locations` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_fk1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_fk2` FOREIGN KEY (`laundry_item_id`) REFERENCES `laundry_items` (`id`) ON DELETE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_fk1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_fk2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_fk1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_fk2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `rider_assignments`
  ADD CONSTRAINT `rider_assignments_fk1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rider_assignments_fk2` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

COMMIT;
