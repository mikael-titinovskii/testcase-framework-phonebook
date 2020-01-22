<?php


namespace app\lib\service\phoneBook;


use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use malkusch\lock\exception\ExecutionOutsideLockException;
use malkusch\lock\exception\LockAcquireException;
use malkusch\lock\exception\LockReleaseException;

class PhoneEntryValidator
{
    private const MSG_PHONE = 'Invalid phone number';
    private const MSG_FIRST_NAME = 'Invalid first name';
    private const MSG_LAST_NAME = 'Invalid last name';
    private const MSG_TZ = 'Invalid timezone name';
    private const MSG_CC = 'Invalid country code';
    /**
     * @var ExternalValidationResourcesService
     */
    private ExternalValidationResourcesService $externalValidationResourcesService;

    /**
     * PhoneEntryValidator constructor.
     * @param ExternalValidationResourcesService $externalValidationResourcesService
     */
    public function __construct(ExternalValidationResourcesService $externalValidationResourcesService)
    {
        $this->externalValidationResourcesService = $externalValidationResourcesService;
    }

    /**
     * @param array $fields
     * @throws ValidationException
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     */
    public function validateFields(array $fields): void
    {
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'first_name':
                    $this->validateFirstName($value);
                    break;
                case 'last_name':
                    $this->validateLastName($value);
                    break;
                case 'country_code':
                    $this->validateCountryCode($value);
                    break;
                case 'phone_name':
                    $this->validatePhoneNumber($value);
                    break;
                case 'timezone_name':
                    $this->validateTimezoneName($value);
                    break;
                default:
                    throw new ValidationException("unknown field {$key}");
            }
        }
    }

    /**
     * @param string $number
     * @return $this
     * @throws ValidationException
     */
    private function validatePhoneNumber(string $number): self
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($number);
            if (!$phoneUtil->isValidNumber($numberProto)) {
                throw new ValidationException(self::MSG_PHONE);
            }
        } catch (NumberParseException $e) {
            throw new ValidationException(self::MSG_PHONE);
        }

        return $this;
    }

    /**
     * @param string $zone
     * @return $this
     * @throws ValidationException
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     */
    private function validateTimezoneName(string $zone): self
    {
        $timezones = $this->externalValidationResourcesService->getTimezones();


        if (!in_array($zone, $timezones, true)) {
            throw new ValidationException(self::MSG_TZ);
        }

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     * @throws ValidationException
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     */
    private function validateCountryCode(string $code): self
    {
        $codes = $this->externalValidationResourcesService->getCountryCodes();

        if (!in_array($code, $codes, true)) {
            throw new ValidationException(self::MSG_CC);
        }

        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     * @throws ValidationException
     */
    private function validateLastName(string $lastName): self
    {
        $this->validateName($lastName, self::MSG_LAST_NAME);

        return $this;
    }

    /**
     * @param string $name
     * @param string $msg
     * @return $this
     * @throws ValidationException
     */
    private function validateName(string $name, string $msg): self
    {
        if (!preg_match('/^[a-zA-Z ]*$/', $name)) {
            throw new ValidationException($msg);
        }

        return $this;
    }

    /**
     * @param string $firstName
     * @return $this
     * @throws ValidationException
     */
    private function validateFirstName(string $firstName): self
    {
        $this->validateName($firstName, self::MSG_FIRST_NAME);

        return $this;
    }
}