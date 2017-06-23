<?php

namespace Fullpipe\Biglion;

/**
 * Biglion api client.
 */
class Client
{
    /**
     * Base api url.
     */
    const BASE_URL = 'http://api.biglion.ru/api.php';

    /**
     * Api vertion.
     */
    const API_VERTION = '2.0';

    /**
     * Api error codes.
     *
     * @var array
     */
    public static $errorCodes = array(
        "-1" => "Неизвестная ошибка",
        "0" => "Ошибок нет Ошибки авторизации",
        "100" => "Такого пользователя не существует",
        "101" => "Пользователь не активирован",
        "102" => "Такой сессии не существует",
        "103" => "Email или пароль введены неверно Ошибки проверки и погашения купона",
        "400" => "Номер невалиден",
        "401" => "Нет предложения с таким кодом",
        "402" => "Предложение не принадлежит компании с указанным ключом",
        "403" => "Купон не существует",
        "404" => "Купон не оплачен",
        "405" => "Купон уже погашен",
        "406" => "Неверный формат даты погашения купона",
        "407" => "Неверный формат суммы чека",
        "408" => "Купон принадлежит другому пользователю",
        "409" => "Заказ не оплачен",
        "410" => "Спецпредложение не завершено",
        "411" => "Прием купонов завершен",
        "412" => "Купон погашен компанией",
        "413" => "Купон уже погашен",
        "414" => "Купон еще не погашен",
        "415" => "Нет прав для просмотра списка купонов",
        "416" => "Нет прав для просмотра статистики",
        "417" => "Не передан пин-код",
        "418" => "Неверный пин-код",
        "419" => "Купон просрочен",
        "420" => "Купон уже забронирован",
        "421" => "Купон еще не забронирован",
        "422" => "Не передан код бронирования",
        "423" => "Неверный код бронирования",
    );

    /**
     * Partner email.
     *
     * @var string
     */
    private $email;

    /**
     * Partner password.
     *
     * @var string
     */
    private $password;

    /**
     * Api key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * Cache for getCompany request.
     *
     * @var array
     */
    private $company;

    /**
     * Constructor.
     *
     * @param string $email
     * @param string $password
     * @param string $apiKey
     */
    public function __construct($email, $password, $apiKey = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->apiKey = $apiKey;
    }

    /**
     * Get company.
     *
     * @return array
     */
    public function getCompany()
    {
        if (null === $this->company) {
            $this->company = $this->doRequest(array(
                'method' => 'get_company',
                'email' => $this->email,
                'password' => $this->password,
                'version' => self::API_VERTION,
                'key' => $this->apiKey,
            ));
        }

        return $this->company;
    }

    /**
     * Get user id from get_company request.
     *
     * @return integer
     */
    private function getUserId()
    {
        $company = $this->getCompany();

        if (!isset($company['user']['id'])) {
            throw new BiglionException('User "id" not defined', 1);
        }

        return $company['user']['id'];
    }

    /**
     * Get user session from get_company request.
     *
     * @return string
     */
    private function getUserSession()
    {
        $company = $this->getCompany();

        if (!isset($company['user']['session'])) {
            throw new BiglionException('User "session" not defined', 1);
        }

        return $company['user']['session'];
    }

    /**
     * Get company api key from get_company request.
     *
     * @return string
     */
    private function getCompanyApiKey()
    {
        $company = $this->getCompany();

        if (!isset($company['company']['api_key'])) {
            throw new BiglionException('Company "api_key" not defined', 1);
        }

        return $company['company']['api_key'];
    }

    /**
     * Get coupon info.
     *
     * @param string $couponNumber
     *
     * @throws \Fullpipe\Biglion\BiglionException on request error
     *
     * @return array
     */
    public function getCouponInfo($couponNumber)
    {
        return $this->doRequest(array(
            'method' => 'redeem_coupon',
            'id' => $this->getUserId(),
            'session' => $this->getUserSession(),
            'key' => $this->getCompanyApiKey(),
            'version' => self::API_VERTION,
            'number' => $couponNumber,
        ));
    }

    /**
     * Redeem coupon.
     *
     * @param string $couponNumber
     * @param string $pincode
     *
     * @throws \Fullpipe\Biglion\BiglionException on request error
     *
     * @return array
     */
    public function redeemCoupon($couponNumber, $pincode)
    {
        $result = $this->doRequest(array(
            'method' => 'redeem_coupon',
            'id' => $this->getUserId(),
            'session' => $this->getUserSession(),
            'key' => $this->getCompanyApiKey(),
            'version' => self::API_VERTION,
            'number' => $couponNumber,
            'pincode' => $pincode,
            'redeem' => true,
        ));

        return $result;
    }

    /**
     * Reserve coupon description].
     *
     * @param string $couponNumber
     * @param string $reserveCode
     *
     * @throws \Fullpipe\Biglion\BiglionException on request error
     *
     * @return array
     */
    public function reserveCoupon($couponNumber, $reserveCode)
    {
        $result = $this->doRequest(array(
            'method' => 'redeem_coupon',
            'id' => $this->getUserId(),
            'session' => $this->getUserSession(),
            'key' => $this->getCompanyApiKey(),
            'version' => self::API_VERTION,
            'number' => $couponNumber,
            'reserve_code' => $reserveCode,
            'reserve' => true,
        ));

        return $result;
    }

    /**
     * Make api request.
     *
     * @param array $queryParams
     *
     * @throws \Fullpipe\Biglion\BiglionException on request error
     *
     * @return array
     */
    private function doRequest(array $queryParams = array())
    {
        $query = array_merge($queryParams, array('type' => 'json'));
        $response = $this->getGuzzleClient()->request('GET', '/api.php', ['query' => $query]);

        $result = json_decode($response->getBody(), true);
        $result = $result['result'];

        if ($result['error'] != 0) {
            throw new BiglionException(self::$errorCodes[(string) $result['error']], $result['error']);
        }

        return $result;
    }

    /**
     * Get Guzzle http client.
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        if (null === $this->guzzleClient) {
            $this->guzzleClient = new \GuzzleHttp\Client(array(
                'base_uri' => self::BASE_URL,
                'timeout' => 5,
            ));
        }

        return $this->guzzleClient;
    }

    /**
     * Set Guzzle http client.
     *
     * @param \GuzzleHttp\Client $guzzleClient
     */
    public function setGuzzleClient(\GuzzleHttp\Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }
}
