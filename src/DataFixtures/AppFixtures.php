<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\CouponType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadProducts($manager);
        $this->loadCoupons($manager);

        $manager->flush();
    }

    private function loadProducts(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Iphone', 'price' => 10000],
            ['name' => 'Наушники', 'price' => 2000],
            ['name' => 'Чехол', 'price' => 1000],
        ];

        foreach ($products as $data) {
            $product = (new Product())
                ->setName($data['name'])
                ->setPrice($data['price']);

            $manager->persist($product);
        }
    }

    private function loadCoupons(ObjectManager $manager): void
    {
        $coupons = [
            ['code' => 'D15', 'type' => CouponType::FIXED, 'value' => 1500],
            ['code' => 'P10', 'type' => CouponType::PERCENTAGE, 'value' => 10],
            ['code' => 'P100', 'type' => CouponType::PERCENTAGE, 'value' => 100],
        ];

        foreach ($coupons as $data) {
            $coupon = (new Coupon())
                ->setCode($data['code'])
                ->setType($data['type'])
                ->setValue($data['value']);

            $manager->persist($coupon);
        }
    }
}
