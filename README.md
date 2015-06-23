# Simple Biglion Api Client

## Usage
```php
$bc = new \Fullpipe\Biglion\Client('email@example.com', 'pa$$word');

try {
    var_dump($bc->getCouponInfo('012345-0000-0001'));
    var_dump($bc->redeemCoupon('012345-0000-0001', '1234'));
    var_dump($bc->reserveCoupon('012345-0000-0001', '387'));
} catch (\Fullpipe\Biglion\BiglionException $e) {
    echo $e->getMessage();
}

try {
    var_dump($bc->getCouponInfo('012345-0000-0002'));
    var_dump($bc->redeemCoupon('012345-0000-0002', '1234'));
    var_dump($bc->reserveCoupon('012345-0000-0002', '387'));
} catch (\Fullpipe\Biglion\BiglionException $e) {
    echo $e->getMessage();
}

try {
    var_dump($bc->getCouponInfo('012345-0000-0003'));
    var_dump($bc->redeemCoupon('012345-0000-0003', '1234'));
    var_dump($bc->reserveCoupon('012345-0000-0003', '387'));
} catch (\Fullpipe\Biglion\BiglionException $e) {
    echo $e->getMessage();
}
```
