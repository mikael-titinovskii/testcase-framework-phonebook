<?php

namespace app\lib\service\phoneBook;

use DateTime;
use DateTimeZone;
use Nette\Database\Table\ActiveRow;

trait MapperTrait
{
    /**
     * @param array $data
     * @return iPhoneEntry
     * @throws ValidationException
     */
    public function mapArrayToEntry(array $data): iPhoneEntry
    {
        $keysToCheck = [
            'phone_number',
            'first_name',
            'last_name',
            'country_code',
            'timezone_name',
        ];

        $hasRequiredData = count(array_intersect(array_keys($data), $keysToCheck)) === count($keysToCheck);
        if (!$hasRequiredData) {
            throw new ValidationException('Required fields - missing');
        }

        return new class($data) implements iPhoneEntry {

            /**
             * @var array
             */
            private array $data;

            public function __construct(array $data)
            {
                $this->data = $data;

            }

            /**
             * @inheritDoc
             */
            public function getId(): ?int
            {
                $value = $this->data['id'];

                if ($value && is_numeric($value)) {
                    return (int)$value;
                }

                return null;
            }

            /**
             * @inheritDoc
             */
            public function getFirstName(): string
            {
                return $this->data['first_name'];
            }

            /**
             * @inheritDoc
             */
            public function getLastName(): string
            {
                return $this->data['last_name'];
            }

            /**
             * @inheritDoc
             */
            public function getPhoneNumber(): string
            {
                return $this->data['phone_number'];
            }

            /**
             * @inheritDoc
             */
            public function getCountryCode(): string
            {
                return $this->data['country_code'];
            }

            /**
             * @inheritDoc
             */
            public function getTimezoneName(): DateTimeZone
            {
                $value = $this->data['timezone_name'];

                return new DateTimeZone($value);
            }

            /**
             * @inheritDoc
             */
            public function getCreatedAt(): ?DateTime
            {
                $value = $this->data['created_at'];

                if ($value) {
                    return new DateTime($value);
                }

                return null;
            }

            /**
             * @inheritDoc
             */
            public function getUpdatedAt(): ?DateTime
            {
                $value = $this->data['updated_at'];

                if ($value) {
                    return new DateTime($value);
                }

                return null;
            }
        };
    }

    /**
     * @param iPhoneEntry $entry
     * @return array
     */
    private function mapEntryToArray(iPhoneEntry $entry): array
    {
        return [
            'id' => $entry->getId(),
            'first_name' => $entry->getFirstName(),
            'last_name' => $entry->getLastName(),
            'phone_number' => $entry->getPhoneNumber(),
            'country_code' => $entry->getCountryCode(),
            'timezone_name' => $entry->getTimezoneName()->getName(),
            'created_at' => $entry->getCreatedAt()
                ? $entry->getCreatedAt()->format(PhoneBookService::DATE_FORMAT)
                : null,
            'updated_at' => $entry->getUpdatedAt()
                ? $entry->getUpdatedAt()->format(PhoneBookService::DATE_FORMAT)
                : null,
        ];
    }

    /**
     * @param ActiveRow $row
     * @return iPhoneEntry
     */
    private function mapActiveRowToEntry(ActiveRow $row): iPhoneEntry
    {
        return new class($row) implements iPhoneEntry {

            /**
             * @var ActiveRow
             */
            private ActiveRow $row;

            public function __construct(ActiveRow $row)
            {
                $this->row = $row;
            }

            /**
             * @inheritDoc
             */
            public function getFirstName(): string
            {
                return $this->row['first_name'];
            }

            /**
             * @inheritDoc
             */
            public function getLastName(): string
            {
                return $this->row['last_name'];
            }

            /**
             * @inheritDoc
             */
            public function getPhoneNumber(): string
            {
                return $this->row['phone_number'];
            }

            /**
             * @inheritDoc
             */
            public function getCountryCode(): string
            {
                return $this->row['country_code'];
            }

            /**
             * @inheritDoc
             */
            public function getTimezoneName(): DateTimeZone
            {
                return new DateTimeZone($this->row['timezone_name']);
            }

            /**
             * @inheritDoc
             */
            public function getCreatedAt(): DateTime
            {
                return DateTime::createFromFormat(PhoneBookService::DATE_FORMAT, $this->row['created_at']);
            }

            /**
             * @inheritDoc
             */
            public function getUpdatedAt(): ?DateTime
            {
                $date = $this->row['updated_at'];

                if ($date) {
                    return DateTime::createFromFormat(PhoneBookService::DATE_FORMAT, $this->row['updated_at']);
                }

                return null;

            }

            /**
             * @inheritDoc
             */
            public function getId(): int
            {
                return $this->row['id'];
            }
        };
    }

}