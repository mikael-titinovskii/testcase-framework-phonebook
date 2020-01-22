<?php


namespace app\lib\service\phoneBook;

use DateTime;
use DateTimeZone;

/**
 * Interface iPhoneEntry
 * @package app\lib\service\phoneBook
 */
interface  iPhoneEntry
{
    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getFirstName(): string;

    /**
     * @return string
     */
    public function getLastName(): string;

    /**
     * @return string
     */
    public function getPhoneNumber(): string;

    /**
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * @return DateTimeZone
     */
    public function getTimezoneName(): DateTimeZone;

    /**
     * @return DateTime
     */
    public function getCreatedAt(): ?DateTime;

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): ?DateTime;

}