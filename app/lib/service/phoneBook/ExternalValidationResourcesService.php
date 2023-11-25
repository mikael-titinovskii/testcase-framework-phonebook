<?php


namespace app\lib\service\phoneBook;


use Cake\Cache\Cache;
use GuzzleHttp\Client;
use malkusch\lock\exception\ExecutionOutsideLockException;
use malkusch\lock\exception\LockAcquireException;
use malkusch\lock\exception\LockReleaseException;
use malkusch\lock\mutex\FlockMutex;

class ExternalValidationResourcesService
{
    private const CC_KEY = 'country_codes';
    private const TZ_KEY = 'timezone_names';
    private const CC_URL_BASE = 'https://api.hostaway.com/countries';
    private const CC_URL_RES = 'countries';
    private const TZ_URL_BASE = 'https://api.hostaway.com/timezones';
    private const TZ_URL_RES = 'timezones';

    private FlockMutex $flockMutex;


    public function __construct(FlockMutex $flockMutex)
    {
        $this->flockMutex = $flockMutex;
    }

    /**
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     */
    public function getCountryCodes()
    {
        $codes = Cache::read(self::CC_KEY);

        if ($codes !== false) {
            return $codes;
        }

        // todo this prob. can be delegated and run in parallel
        // we do a mutex here to warm up cache, in expense of first customer's time
        // todo i guess this could be run via cli + cron / composer script idk
        $this->flockMutex->synchronized(
            static function () use (&$codes) {
                $client = new Client(['base_uri' => self::CC_URL_BASE]);
                $response = $client->request('GET', self::CC_URL_RES);
                $decoded = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
                $target = $decoded['result'];
                $codes = array_keys($target);
            }
        );

        Cache::write(self::CC_KEY, $codes);

        return $codes;
    }

    /**
     * @throws ExecutionOutsideLockException
     * @throws LockAcquireException
     * @throws LockReleaseException
     */
    public function getTimezones()
    {
        $zones = Cache::read(self::TZ_KEY);

        if ($zones !== false) {
            return $zones;
        }

        $this->flockMutex->synchronized(
            static function () use (&$zones) {
                $client = new Client(['base_uri' => self::TZ_URL_BASE]);
                $response = $client->request('GET', self::TZ_URL_RES);
                $decoded = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
                $target = $decoded['result'];
                $zones = array_keys($target);
            }
        );

        Cache::write(self::TZ_KEY, $zones);

        return $zones;
    }
}