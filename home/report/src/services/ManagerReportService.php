<?php

/**
 * -----------| 服务类:所有报表Service的管理类 |-----------
 * @category report
 * @package services
 * @author skygreen skygreen2001@gmail.com
 */
class ManagerReportService extends Manager
{
    private static $reportoneService;

    /**
     * 提供服务: 统一报表
     */
    public static function serviceReportone()
    {
        if (self::$reportoneService == null) {
            self::$reportoneService = new ServiceReportone();
        }
        return self::$reportoneService;
    }
}
