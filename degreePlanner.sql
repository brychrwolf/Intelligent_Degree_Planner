-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 20, 2012 at 03:34 PM
-- Server version: 5.5.11
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `adv_axiom`
--

CREATE TABLE IF NOT EXISTS `adv_axiom` (
  `AXI_ID` varchar(64) NOT NULL,
  `AXI_VALUE` int(11) NOT NULL,
  `AXI_DESC` text NOT NULL,
  PRIMARY KEY (`AXI_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_class`
--

CREATE TABLE IF NOT EXISTS `adv_class` (
  `CLS_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CRS_ID` varchar(4) NOT NULL,
  `CLS_TERM` int(11) NOT NULL,
  `CLS_DAY` enum('M','T','W','R','F','S') DEFAULT NULL,
  `CLS_START` time DEFAULT NULL,
  `CLS_END` time DEFAULT NULL,
  PRIMARY KEY (`CLS_ID`),
  KEY `CRS_ID` (`CRS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `adv_course`
--

CREATE TABLE IF NOT EXISTS `adv_course` (
  `CRS_ID` varchar(8) NOT NULL,
  `CRS_ALPHA` varchar(4) NOT NULL,
  `CRS_NUM` varchar(4) NOT NULL,
  `CRS_NAME` varchar(64) NOT NULL,
  `CRS_CREDIT` int(1) NOT NULL,
  `CRS_DESC` text NOT NULL,
  PRIMARY KEY (`CRS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_degree`
--

CREATE TABLE IF NOT EXISTS `adv_degree` (
  `DEG_ID` varchar(12) NOT NULL,
  `DEG_NAME` varchar(64) NOT NULL,
  PRIMARY KEY (`DEG_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_degreq`
--

CREATE TABLE IF NOT EXISTS `adv_degreq` (
  `DEG_ID` varchar(12) NOT NULL,
  `CRS_ID` varchar(12) NOT NULL,
  KEY `DEG_ID` (`DEG_ID`),
  KEY `CRS_ID` (`CRS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_earned`
--

CREATE TABLE IF NOT EXISTS `adv_earned` (
  `STU_ID` int(11) NOT NULL,
  `CRS_ID` varchar(8) NOT NULL,
  `ERN_TERM` int(11) NOT NULL,
  `ERN_CREDITS` decimal(10,3) NOT NULL,
  `ERN_CR_TYPE` enum('H','T','N') NOT NULL COMMENT 'HPU, Transfer, Non-Traditional',
  UNIQUE KEY `STU_CRS` (`STU_ID`,`CRS_ID`),
  KEY `STU_ID` (`STU_ID`),
  KEY `CRS_ID` (`CRS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_gedreq`
--

CREATE TABLE IF NOT EXISTS `adv_gedreq` (
  `GED_ID` varchar(2) NOT NULL,
  `GED_CAT` varchar(1) NOT NULL,
  `CRS_ID` varchar(8) NOT NULL,
  KEY `GED_ID` (`GED_ID`),
  KEY `CRS_ID` (`CRS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_gedreq_assoc`
--

CREATE TABLE IF NOT EXISTS `adv_gedreq_assoc` (
  `DEG_ID` varchar(12) NOT NULL,
  `GED_ID` varchar(2) NOT NULL,
  `GED_CAT` varchar(1) NOT NULL,
  KEY `DEG_ID` (`DEG_ID`),
  KEY `GED_ID` (`GED_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_gedtheme`
--

CREATE TABLE IF NOT EXISTS `adv_gedtheme` (
  `GED_ID` varchar(2) NOT NULL,
  `GED_NAME` varchar(64) NOT NULL,
  PRIMARY KEY (`GED_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_prereq`
--

CREATE TABLE IF NOT EXISTS `adv_prereq` (
  `CRS_ID` varchar(8) NOT NULL,
  `PRQ_ID` varchar(8) NOT NULL,
  KEY `CRS_ID` (`CRS_ID`),
  KEY `PRQ_ID` (`PRQ_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `adv_student`
--

CREATE TABLE IF NOT EXISTS `adv_student` (
  `STU_ID` int(11) NOT NULL AUTO_INCREMENT,
  `STU_LNAME` varchar(24) NOT NULL,
  `STU_FNAME` varchar(24) NOT NULL,
  `DEG_ID` varchar(12) NOT NULL,
  `STU_START_TERM` int(11) NOT NULL,
  `STU_SCHED` enum('MAIN','MCP','COMBO') NOT NULL,
  `STU_SCHED_OPT` tinyint(1) NOT NULL COMMENT 'If true, include Interim/Summer',
  PRIMARY KEY (`STU_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `adv_term`
--

CREATE TABLE IF NOT EXISTS `adv_term` (
  `TERM_ADDEND` int(11) NOT NULL,
  `TERM_SCHED` enum('MCP','MCP_OPT','MAIN','MAIN_OPT') NOT NULL,
  `TERM_NAME` varchar(16) NOT NULL,
  `TERM_FULL_LOAD` int(11) NOT NULL,
  `TERM_MAX_LOAD` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adv_class`
--
ALTER TABLE `adv_class`
  ADD CONSTRAINT `adv_class_ibfk_1` FOREIGN KEY (`CRS_ID`) REFERENCES `adv_course` (`CRS_ID`);

--
-- Constraints for table `adv_degreq`
--
ALTER TABLE `adv_degreq`
  ADD CONSTRAINT `adv_degreq_ibfk_1` FOREIGN KEY (`DEG_ID`) REFERENCES `adv_degree` (`DEG_ID`),
  ADD CONSTRAINT `adv_degreq_ibfk_2` FOREIGN KEY (`CRS_ID`) REFERENCES `adv_course` (`CRS_ID`);

--
-- Constraints for table `adv_earned`
--
ALTER TABLE `adv_earned`
  ADD CONSTRAINT `adv_earned_ibfk_1` FOREIGN KEY (`STU_ID`) REFERENCES `adv_student` (`STU_ID`),
  ADD CONSTRAINT `adv_earned_ibfk_2` FOREIGN KEY (`CRS_ID`) REFERENCES `adv_course` (`CRS_ID`);

--
-- Constraints for table `adv_gedreq`
--
ALTER TABLE `adv_gedreq`
  ADD CONSTRAINT `adv_gedreq_ibfk_1` FOREIGN KEY (`GED_ID`) REFERENCES `adv_gedtheme` (`GED_ID`),
  ADD CONSTRAINT `adv_gedreq_ibfk_2` FOREIGN KEY (`CRS_ID`) REFERENCES `adv_course` (`CRS_ID`);

--
-- Constraints for table `adv_gedreq_assoc`
--
ALTER TABLE `adv_gedreq_assoc`
  ADD CONSTRAINT `adv_gedreq_assoc_ibfk_1` FOREIGN KEY (`GED_ID`) REFERENCES `adv_gedtheme` (`GED_ID`),
  ADD CONSTRAINT `adv_gedreq_assoc_ibfk_2` FOREIGN KEY (`DEG_ID`) REFERENCES `adv_degree` (`DEG_ID`);

--
-- Constraints for table `adv_prereq`
--
ALTER TABLE `adv_prereq`
  ADD CONSTRAINT `adv_prereq_ibfk_1` FOREIGN KEY (`CRS_ID`) REFERENCES `adv_course` (`CRS_ID`),
  ADD CONSTRAINT `adv_prereq_ibfk_2` FOREIGN KEY (`PRQ_ID`) REFERENCES `adv_course` (`CRS_ID`);
