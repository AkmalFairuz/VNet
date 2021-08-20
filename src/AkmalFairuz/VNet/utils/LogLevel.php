<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\utils;

interface LogLevel{
    const EMERGENCY = "emergency";
    const ALERT = "alert";
    const CRITICAL = "critical";
    const ERROR = "error";
    const WARNING = "warning";
    const NOTICE = "notice";
    const INFO = "info";
    const DEBUG = "debug";
}