-- phpMyAdmin SQL Dump
-- version 4.3.12
-- http://www.phpmyadmin.net
--
-- Host: viper.dns-systems.net
-- Generation Time: Apr 28, 2015 at 12:30 AM
-- Server version: 5.5.43
-- PHP Version: 5.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `twinklebob_lyv`
--

-- --------------------------------------------------------

--
-- Table structure for table `DB_AUDIT`
--

CREATE TABLE IF NOT EXISTS `DB_AUDIT` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `tablename` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `rowid` int(11) NOT NULL,
  `action` varchar(50) COLLATE latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE IF NOT EXISTS `collection` (
  `collectionid` int(11) NOT NULL,
  `collectionguid` varchar(36) COLLATE latin1_general_ci NOT NULL,
  `createdate` datetime NOT NULL,
  `createuser` int(11) DEFAULT '1',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) DEFAULT '1',
  `libraryid` int(11) NOT NULL,
  `collectionname` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `collectiontype` int(11) NOT NULL,
  `location` varchar(255) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collectionitem`
--

CREATE TABLE IF NOT EXISTS `collectionitem` (
  `collectionitemid` int(11) NOT NULL,
  `itemguid` varchar(36) COLLATE latin1_general_ci NOT NULL,
  `createdate` datetime NOT NULL,
  `createuser` int(11) DEFAULT '1',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) DEFAULT '1',
  `collectionid` int(11) NOT NULL,
  `itemreference` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `itemname` varchar(512) COLLATE latin1_general_ci NOT NULL,
  `attribution` varchar(512) COLLATE latin1_general_ci DEFAULT NULL,
  `location` varchar(512) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itemmeta`
--

CREATE TABLE IF NOT EXISTS `itemmeta` (
  `itemmetaid` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `createdate` datetime NOT NULL,
  `createuser` int(11) DEFAULT '1',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) DEFAULT '1',
  `metakey` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `metavalue` varchar(255) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `library`
--

CREATE TABLE IF NOT EXISTS `library` (
  `libraryid` int(11) NOT NULL,
  `libguid` varchar(36) COLLATE latin1_general_ci NOT NULL DEFAULT 'SELECT UUID()',
  `createdate` datetime NOT NULL,
  `createuser` int(11) NOT NULL DEFAULT '0',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) NOT NULL DEFAULT '0',
  `name` varchar(256) COLLATE latin1_general_ci NOT NULL,
  `location` varchar(256) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `libraryuser`
--

CREATE TABLE IF NOT EXISTS `libraryuser` (
  `libraryuserid` int(11) NOT NULL,
  `createdate` datetime NOT NULL,
  `createuser` int(11) DEFAULT '1',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) DEFAULT '1',
  `libraryid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `usertype` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lyvuser`
--

CREATE TABLE IF NOT EXISTS `lyvuser` (
  `lyvuserid` int(11) NOT NULL,
  `createdate` datetime NOT NULL,
  `createuser` int(11) DEFAULT '1',
  `modifydate` datetime NOT NULL,
  `modifyuser` int(11) DEFAULT '1',
  `firstname` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `surname` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `email` varchar(512) COLLATE latin1_general_ci NOT NULL,
  `password_hash` varchar(512) COLLATE latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schemaversion`
--

CREATE TABLE IF NOT EXISTS `schemaversion` (
  `version` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usertype`
--

CREATE TABLE IF NOT EXISTS `usertype` (
  `usertypeid` int(11) NOT NULL,
  `description` varchar(255) COLLATE latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `DB_AUDIT`
--
ALTER TABLE `DB_AUDIT`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collectionid`), ADD UNIQUE KEY `collectionguid` (`collectionguid`);

--
-- Indexes for table `collectionitem`
--
ALTER TABLE `collectionitem`
  ADD PRIMARY KEY (`collectionitemid`), ADD UNIQUE KEY `itemguid` (`itemguid`), ADD KEY `collectionid` (`collectionid`);

--
-- Indexes for table `itemmeta`
--
ALTER TABLE `itemmeta`
  ADD PRIMARY KEY (`itemmetaid`);

--
-- Indexes for table `library`
--
ALTER TABLE `library`
  ADD PRIMARY KEY (`libraryid`), ADD UNIQUE KEY `libguid` (`libguid`), ADD KEY `name` (`name`);

--
-- Indexes for table `libraryuser`
--
ALTER TABLE `libraryuser`
  ADD PRIMARY KEY (`libraryuserid`);

--
-- Indexes for table `lyvuser`
--
ALTER TABLE `lyvuser`
  ADD PRIMARY KEY (`lyvuserid`), ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `usertype`
--
ALTER TABLE `usertype`
  ADD PRIMARY KEY (`usertypeid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `DB_AUDIT`
--
ALTER TABLE `DB_AUDIT`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

INSERT INTO schemaversion (version) VALUES (1);