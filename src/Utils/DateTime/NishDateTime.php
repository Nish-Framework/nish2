<?php
namespace Nish\Utils\DateTime;


class NishDateTime
{
    private static $timezone;

    /* @var \DateTime */
    private static $dateTimeObj = null;

    /* @var \DateTimeZone */
    private static $dateTimeZoneObj = null;

    /**
     *
     */
    private static function setDateTimeZoneObj()
    {
        try{
            self::$dateTimeZoneObj = new \DateTimeZone(self::getTimezone());
        }
        catch(\Exception $e){
            self::$dateTimeZoneObj = new \DateTimeZone(date_default_timezone_get());
        }
    }

    /**
     * @throws \Exception
     */
    private static function setDateTimeObj(){
        if(self::$dateTimeObj == null){
            self::$dateTimeObj = new \DateTime();

            self::setDateTimeZoneObj();

            self::$dateTimeObj->setTimezone(self::$dateTimeZoneObj);
        }
    }

    /**
     * @return mixed
     */
    public static function getTimezone()
    {
        if (empty(self::$timezone)) {
            self::$timezone = date_default_timezone_get();
        }

        return self::$timezone;
    }

    /**
     * @param mixed $timezone
     */
    public static function setTimezone($timezone)
    {
        self::$timezone = $timezone;

        if(self::$dateTimeObj instanceof \DateTime)
        {
            self::setDateTimeZoneObj();

            self::$dateTimeObj->setTimezone(self::$dateTimeZoneObj);
        }
    }


    /**
     * @param $UnixTimestamp
     * @param string $Format
     * @return string
     * @throws \Exception
     */
    public static function format($UnixTimestamp, string $Format){
        self::setDateTimeObj();

        self::$dateTimeObj->setTimestamp($UnixTimestamp);
        return self::$dateTimeObj->format($Format);
    }

    /**
     * @param $Hour
     * @param $Minute
     * @param $Second
     * @param $Month
     * @param $Day
     * @param $Year
     * @return int
     * @throws \Exception
     */
    public static function makeTimestamp($Hour, $Minute, $Second, $Month, $Day, $Year){
        self::setDateTimeObj();

        self::$dateTimeObj->setDate($Year, $Month, $Day);
        self::$dateTimeObj->setTime($Hour,$Minute,$Second);
        return self::$dateTimeObj->getTimestamp();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getTimestamp(){
        self::setDateTimeObj();

        return self::$dateTimeObj->getTimestamp();
    }

    /**
     * @return int
     */
    public static function getCurrentTimestamp(){
        $dtObj = new \DateTime('now');
        self::setDateTimeZoneObj();
        $dtObj->setTimezone(self::$dateTimeZoneObj);
        return $dtObj->getTimestamp();
    }

    /**
     * @return string
     */
    public static function getTimezoneName(){
        self::setDateTimeZoneObj();

        return self::$dateTimeZoneObj->getName();
    }

    /**
     * @param null $UnixTimeStamp
     * @return array
     * @throws \Exception
     */
    public static function getDayBoundariesOfTime($UnixTimeStamp = null){
        if(!is_numeric($UnixTimeStamp)){
            $UnixTimeStamp = self::getCurrentTimestamp();
        }
        $toks = explode('.', self::format($UnixTimeStamp,'d.m.Y'));

        return array(
            self::makeTimestamp(0,0,0,$toks[1], $toks[0], $toks[2]),
            self::makeTimestamp(23,59,59,$toks[1], $toks[0], $toks[2])
        );
    }

    /**
     * @param $dateTime
     * @param false $startFromStatedDay
     * @param null $dateTimeFormat
     * @param null $add
     * @return array
     * @throws \Exception
     */
    public static function getMonthBoundariesTimestamps($dateTime, $startFromStatedDay = false, $dateTimeFormat = null, $add = null)
    {
        self::setDateTimeObj();

        if (!empty($dateTimeFormat)) {
            $d = \DateTime::createFromFormat($dateTimeFormat, $dateTime);

            self::$dateTimeObj->setTimestamp($d->getTimestamp());
        } else if (is_numeric($dateTime)) {
            self::$dateTimeObj->setTimestamp($dateTime);
        } else {
            $d = new \DateTime($dateTime);
            self::$dateTimeObj->setTimestamp($d->getTimestamp());
        }

        if (!empty($add)) {
            self::$dateTimeObj->add(new \DateInterval($add));
        }

        if (!$startFromStatedDay) {
            self::$dateTimeObj->modify('first day of this month');
        }

        $firstDay = self::getTimestampOfDateStr(self::$dateTimeObj->format('d.m.Y').' 00:00:00');

        self::$dateTimeObj->modify('last day of this month');

        $lastDay = self::getTimestampOfDateStr(self::$dateTimeObj->format('d.m.Y').' 23:59:59');

        return [$firstDay, $lastDay];
    }

    /**
     * @param $dateTime
     * @param null $dateTimeFormat
     * @return array
     * @throws \Exception
     */
    public static function getDayBoundariesTimestamps($dateTime, $dateTimeFormat = null)
    {
        self::setDateTimeObj();

        if (!empty($dateTimeFormat)) {
            $d = \DateTime::createFromFormat($dateTimeFormat, $dateTime);

            self::$dateTimeObj->setTimestamp($d->getTimestamp());
        } else if (is_numeric($dateTime)) {
            self::$dateTimeObj->setTimestamp($dateTime);
        } else {
            $d = new \DateTime($dateTime);
            self::$dateTimeObj->setTimestamp($d->getTimestamp());
        }

        $firstTime = self::getTimestampOfDateStr(self::$dateTimeObj->format('d.m.Y').' 00:00:00');

        $lastTime = self::getTimestampOfDateStr(self::$dateTimeObj->format('d.m.Y').' 23:59:59');

        return [$firstTime, $lastTime];
    }

    /**
     * @param $dateTimeString
     * @param false $startFromStatedDay
     * @param null $dateTimeFormat
     * @param null $add
     * @param string $returnFormat
     * @return array
     * @throws \Exception
     */
    public static function getMonthBoundaries($dateTimeString, $startFromStatedDay = false, $dateTimeFormat = null, $add = null, $returnFormat = 'd.m.Y')
    {
        $boundaries = self::getMonthBoundariesTimestamps($dateTimeString, $startFromStatedDay, $dateTimeFormat, $add);

        return [
            date($returnFormat, $boundaries[0]),
            date($returnFormat, $boundaries[1])
        ];
    }

    /**
     * @param $dateStr
     * @param string $format
     * @return int
     * @throws \Exception
     */
    private static function getTimestampOfDateStr($dateStr, $format = 'd.m.Y H:i:s')
    {
        $obj = (\DateTime::createFromFormat($format, $dateStr, self::$dateTimeObj->getTimezone()));

        if ($obj === false) {
            throw new \Exception('Invalid date or format! DateStr: '.$dateStr.', Format: '.$format);
        } else {
            return $obj->getTimestamp();
        }
    }

    /**
     * @param $firstTimestamp
     * @param $secondTimestamp
     * @param bool $ceil
     * @return false|float|int
     */
    public static function getDayDiff($firstTimestamp, $secondTimestamp, $ceil = true)
    {
        $hoursDiff = ($secondTimestamp - $firstTimestamp) / 3600;
        $diff = $hoursDiff / 24;

        if ($ceil) {
            $dayDiff = floor($diff);

            $div = floor($hoursDiff/24);
            $remainingHours = $hoursDiff - (24 * $div);

            if ($remainingHours > 3) {
                $dayDiff++;
            }
            return $dayDiff;
        } else {
            return $diff;
        }
    }
}