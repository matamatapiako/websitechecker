
--
-- Table structure for table `change_log`
--

CREATE TABLE `change_log` (
  `id` int(11) NOT NULL,
  `webpage_id` int(11) NOT NULL,
  `content_hash` varchar(64) NOT NULL,
  `time_detected` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `domains`
--

CREATE TABLE `domains` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `domain_name` varchar(150) NOT NULL,
  `last_whois_check` datetime NOT NULL,
  `last_ns_check` datetime NOT NULL,
  `whois_data` text NOT NULL,
  `ns_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `webpage_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `name` varchar(30) NOT NULL,
  `filename` varchar(30) NOT NULL,
  `show_on_menu` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Dumping data for table `page`
--

INSERT INTO `page` (`id`, `title`, `name`, `filename`, `show_on_menu`) VALUES
(1, 'Sites', 'sites', 'sites.php', 1),
(2, 'Webpages', 'pages', 'pages.php', 1),
(3, 'Domains', 'domains', 'domains.php', 1),
(4, 'Change History', 'changes', 'changes.php', 0),
(5, 'Reminders', 'reminders', 'reminders.php', 1),
(6, 'Subscribers', 'subscribers', 'subscribers.php', 1),
(7, 'Validation Rules', 'validator_rules', 'validator_rules.php', 0),
(8, 'Tasks', 'tasks', 'tasks.php', 1),
(9, 'Users', 'users', 'users.php', 1),
(10, 'Response Times', 'responsetimes', 'responsetimes.php', 0);


--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `reminder_time` datetime NOT NULL,
  `webpage_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `subscriber_comment` text NOT NULL,
  `subscriber_decision` varchar(255) NOT NULL,
  `admin_comment` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `status_viewed_date` datetime NOT NULL,
  `status_checked_date` datetime NOT NULL,
  `status_completed_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `response`
--

CREATE TABLE `response` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `timechecked` datetime NOT NULL,
  `response_time` float NOT NULL COMMENT 'microseconds',
  `response_code` varchar(4) NOT NULL,
  `comment` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `primary_domain` int(11) NOT NULL,
  `description` text NOT NULL,
  `enabled` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `subscriber`
--

CREATE TABLE `subscriber` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `webpage_id` int(11) NOT NULL,
  `notify_on_update` tinyint(1) NOT NULL,
  `last_update_sent` datetime NOT NULL,
  `remind_to_update` tinyint(1) NOT NULL,
  `reminder_time` int(11) NOT NULL,
  `last_reminder_sent` datetime NOT NULL,
  `next_reminder_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `id` int(11) NOT NULL,
  `taskname` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `taskfile` varchar(50) NOT NULL,
  `last_runtime` datetime NOT NULL,
  `next_runtime` datetime NOT NULL,
  `run_interval` varchar(50) NOT NULL COMMENT 'as json {"days":0,"hours":0,"minutes":0}',
  `active` tinyint(1) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Dumping data for table `task`
--

INSERT INTO `task` (`id`, `taskname`, `description`, `taskfile`, `last_runtime`, `next_runtime`, `run_interval`, `active`, `status`) VALUES
(1, 'check_pages', 'Check the pages older than X days for changes', 'check_pages.php', '2017-09-26 08:15:33', '2017-09-26 08:30:33', '{\"days\":0,\"hours\":0,\"minutes\":15}', 1, 'idle'),
(2, 'automatic_reminders', 'Automatically remind subscribers to check their page and suggest improvements to their content.', 'automatic_reminders.php', '2017-09-26 08:17:24', '2017-09-27 08:17:24', '{\"days\":0,\"hours\":24,\"minutes\":0}', 1, 'idle'),
(3, 'cleanup', 'Clean up the system ', 'cleanup.php', '2017-09-26 08:17:24', '2017-09-26 08:17:24', '{\"days\":0,\"hours\":0,\"minutes\":0}', 1, 'idle');


--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `realname` varchar(100) NOT NULL,
  `email_address` varchar(150) NOT NULL,
  `notifications` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `realname`, `email_address`, `notifications`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator - default password is admin', '', 0);


--
-- Table structure for table `versions`
--

CREATE TABLE `versions` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `version_hash` varchar(64) NOT NULL,
  `date_version_captured` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `webpages`
--

CREATE TABLE `webpages` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `page_url` text NOT NULL,
  `last_checked` datetime NOT NULL,
  `last_changed` datetime NOT NULL,
  `latest_response_code` varchar(100) NOT NULL,
  `current_hash` varchar(64) NOT NULL,
  `use_https` tinyint(1) NOT NULL DEFAULT '0',
  `keep_history` tinyint(1) NOT NULL,
  `version_detection` text NOT NULL COMMENT 'as json ',
  `version_count` int(11) NOT NULL,
  `monitor_response` tinyint(1) NOT NULL,
  `response_threshold` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Indexes for table `change_log`
--
ALTER TABLE `change_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `response`
--
ALTER TABLE `response`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriber`
--
ALTER TABLE `subscriber`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `versions`
--
ALTER TABLE `versions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webpages`
--
ALTER TABLE `webpages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`domain_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `change_log`
--
ALTER TABLE `change_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `domains`
--
ALTER TABLE `domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `page`
--
ALTER TABLE `page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `response`
--
ALTER TABLE `response`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `subscriber`
--
ALTER TABLE `subscriber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `versions`
--
ALTER TABLE `versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `webpages`
--
ALTER TABLE `webpages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;



