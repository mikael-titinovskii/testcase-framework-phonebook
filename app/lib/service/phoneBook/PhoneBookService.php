<?php

namespace app\lib\service\phoneBook;

use DateTime;
use Exception;
use League\Route\Http\Exception\NotFoundException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class PhoneBookService
{
    use MapperTrait;

    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Context
     */
    private Context $context;

    /**
     * PhoneBookService constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param int $id
     * @return iPhoneEntry
     * @throws NotFoundException
     */
    public function getById(int $id): iPhoneEntry
    {
        $pb = $this->context->table('phone_book');
        $entry = $pb->get($id);

        if (!$entry) {
            throw new NotFoundException('Phone book entry not found');
        }

        return $this->mapActiveRowToEntry($entry);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getSearchableParams(): array
    {
        return array_map(
            fn(ReflectionMethod $entry): string => $entry->name,
            (new ReflectionClass(iSearchParams::class))->getMethods()
        );
    }

    /**
     * @param iSearchParams $searchParams
     * @return array
     */
    public function getBySearchParams(iSearchParams $searchParams): array
    {
        $pb = $this->context->table('phone_book');

        if ($searchParams->byFirstOrLastName()) {
            $pb->whereOr(
                [
                    'first_name LIKE ?' => sprintf('%%%s%%', $searchParams->byFirstOrLastName()), //lol
                    'last_name LIKE ?' => sprintf('%%%s%%', $searchParams->byFirstOrLastName()),
                ]
            );
        }

        if ($searchParams->byPhone()) {
            $pb->where('phone_number LIKE ?', sprintf('%%%s%%', $searchParams->byPhone()));
        }

        return array_map(
            fn(ActiveRow $r) => $this->mapActiveRowToEntry($r),
            iterator_to_array($pb)
        );
    }

    /**
     * @param array $fields
     * @return iPhoneEntry
     * @throws Exception
     */
    public function addEntry(array $fields): iPhoneEntry
    {
        $fields['created_at'] = (new DateTime())->format(self::DATE_FORMAT);
        $row = $this->context->table('phone_book')->insert($fields);

        return $this->mapActiveRowToEntry($row);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateEntry(int $id, array $data): bool
    {
        if (isset($data['created_at'])) {
            unset($data['created_at']);
        }
        $data['updated_at'] = (new DateTime())->format(self::DATE_FORMAT);

        $affectedRows = $this->context->table('phone_book')
            ->where('id', $id)
            ->update($data);

        return $affectedRows === 1;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function removeEntry(int $id): bool
    {
        $affectedRows = $this->context->table('phone_book')
            ->where('id', $id)
            ->delete();

        return $affectedRows === 1;
    }
}