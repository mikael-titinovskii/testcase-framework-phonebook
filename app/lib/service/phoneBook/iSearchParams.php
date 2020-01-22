<?php


namespace app\lib\service\phoneBook;

/**
 * Interface iSearchParams
 * @package app\lib\service\phoneBook
 */
interface iSearchParams
{
    /**
     * @return string|null
     */
    public function byFirstOrLastName(): ?string;

    /**
     * @return string|null
     */
    public function byPhone(): ?string;
}