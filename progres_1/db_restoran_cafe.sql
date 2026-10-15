-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 08, 2026 at 02:57 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `db_restoran_cafe` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `db_restoran_cafe`;

CREATE TABLE `area` (
  `id_area` int NOT NULL,
  `nama_area` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `area` (`id_area`, `nama_area`) VALUES
(1, 'Indoor'),
(2, 'Outdoor'),
(3, 'VIP');

CREATE TABLE `meja` (
  `id_meja` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `id_area` int NOT NULL,
  `kapasitas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


INSERT INTO `meja` (`id_meja`, `nomor_meja`, `id_area`, `kapasitas`) VALUES
(1, 1, 1, 3),
(2, 2, 1, 4),
(3, 3, 1, 2),
(4, 4, 2, 4),
(5, 5, 2, 6),
(6, 6, 3, 8);

CREATE TABLE `reservasi` (
  `id_reservasi` int NOT NULL,
  `id_meja` int NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `jumlah_orang` int NOT NULL,
  `status` enum('aktif','batal') NOT NULL DEFAULT 'aktif',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `area`
  ADD PRIMARY KEY (`id_area`);

ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`),
  ADD KEY `id_area` (`id_area`);

ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `id_meja` (`id_meja`);


ALTER TABLE `area`
  MODIFY `id_area` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `meja`
  MODIFY `id_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `meja`
  ADD CONSTRAINT `meja_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `area` (`id_area`);

ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`);
COMMIT;
