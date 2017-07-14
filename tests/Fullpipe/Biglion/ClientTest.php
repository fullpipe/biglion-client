<?php

namespace tests\Fullpipe\Biglion;

use Fullpipe\Biglion\BiglionException;
use Fullpipe\Biglion\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        if (!getenv('BIGLION_EMAIL') || !getenv('BIGLION_PASSWORD')) {
            $this->markTestSkipped(
              'Envars "BIGLION_EMAIL" and "BIGLION_PASSWORD" are required.'
            );
        }

        $this->client = new Client(
            getenv('BIGLION_EMAIL'),
            getenv('BIGLION_PASSWORD'),
            getenv('BIGLION_API_KEY')
        );
    }

    public function tearDown()
    {
        $this->client = null;
    }

    public function testGetCompany()
    {
        $company = $this->client->getCompany();
        $this->assertArrayHasKey('user', $company);
        $this->assertArrayHasKey('company', $company);
        $this->assertArrayHasKey('user', $company);
        $this->assertArrayHasKey('error', $company);
        $this->assertEquals(0, $company['error']);
    }

    public function testGetCouponInfo()
    {
        $coupon1 = $this->client->getCouponInfo('012345-0000-0001');
        $this->assertEquals(0, $coupon1['status']);
        $this->assertEquals(0, $coupon1['reserved']);
        $this->assertEquals(0, $coupon1['error']);

        $coupon2 = $this->client->getCouponInfo('012345-0000-0002');
        $this->assertEquals(1, $coupon2['status']);
        $this->assertEquals(0, $coupon2['reserved']);
        $this->assertEquals(0, $coupon2['error']);

        $coupon3 = $this->client->getCouponInfo('012345-0000-0003');
        $this->assertEquals(0, $coupon3['status']);
        $this->assertEquals(1, $coupon3['reserved']);
        $this->assertEquals(0, $coupon3['error']);
    }

    public function testRedeemGoodCoupon()
    {
        $coupon = $this->client->redeemCoupon('012345-0000-0001', '1234');
        $this->assertEquals(1, $coupon['status']);
        $this->assertEquals(0, $coupon['reserved']);
        $this->assertEquals(0, $coupon['error']);
    }

    public function testRedeemGoodCouponFail()
    {
        $this->expectException(BiglionException::class);
        $coupon = $this->client->redeemCoupon('012345-0000-0001', 'invalid pin');
    }

    public function testRedeemRedeemedCoupon()
    {
        $this->expectException(BiglionException::class);
        $coupon = $this->client->redeemCoupon('012345-0000-0002', '1234');
    }

    public function testRedeemReservedCoupon()
    {
        $coupon = $this->client->redeemCoupon('012345-0000-0003', '1234');
        $this->assertEquals(1, $coupon['status']);
        $this->assertEquals(1, $coupon['reserved']);
        $this->assertEquals(0, $coupon['error']);
    }

    public function testReserveGoodCoupon()
    {
        $coupon = $this->client->reserveCoupon('012345-0000-0001', '387');
        $this->assertEquals(0, $coupon['status']);
        $this->assertEquals(1, $coupon['reserved']);
        $this->assertEquals(0, $coupon['error']);
    }

    public function testReserveGoodCouponFail()
    {
        $this->expectException(BiglionException::class);
        $coupon = $this->client->reserveCoupon('012345-0000-0001', 'invalid code');
    }

    public function testReserveRedeemedCoupon()
    {
        $coupon = $this->client->reserveCoupon('012345-0000-0002', '387');

        $this->assertEquals(1, $coupon['status']);
        $this->assertEquals(1, $coupon['reserved']);
        $this->assertEquals(0, $coupon['error']);
    }

    public function testReserveReservedCoupon()
    {
        $this->expectException(BiglionException::class);
        $coupon = $this->client->reserveCoupon('012345-0000-0003', '387');
    }
}
