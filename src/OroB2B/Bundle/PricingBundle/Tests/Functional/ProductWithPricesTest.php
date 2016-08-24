<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Model\FallbackType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\ProductBundle\Entity\Product;

/**
 * @dbIsolation
 */
class ProductWithPricesTest extends WebTestCase
{
    const TEST_SKU = 'SKU-001';

    const PRICE_LIST_NAME = 'price_list_1';

    const FIRST_UNIT_CODE = 'item';
    const FIRST_UNIT_FULL_NAME = 'item';
    const FIRST_UNIT_PRECISION = 0;
    const SECOND_UNIT_CODE = 'kg';
    const SECOND_UNIT_FULL_NAME = 'kilogram';
    const SECOND_UNIT_PRECISION = 3;

    const FIRST_QUANTITY = 10;
    const SECOND_QUANTITY = 5.555556;
    const EXPECTED_SECOND_QUANTITY = 5.556;

    const FIRST_PRICE_VALUE = 10;
    const FIRST_PRICE_CURRENCY = 'USD';
    const SECOND_PRICE_VALUE = 0.5;
    const SECOND_PRICE_CURRENCY = 'USD';

    const DEFAULT_NAME = 'default name';

    const CATEGORY_ID = 1;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(['OroB2B\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadPriceLists']);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orob2b_product_create'));
        $form = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'orob2b_product_create';
        $formValues['orob2b_product_step_one']['category'] = self::CATEGORY_ID;

        $this->client->followRedirects(true);
        $crawler = $this->client->request('POST', $this->getUrl('orob2b_product_create'), $formValues);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        /** @var PriceList $priceList */
        $priceList = $this->getReference(self::PRICE_LIST_NAME);

        $this->client->followRedirects(true);

        $localizations = $this->getLocalizations();

        $formData = [
            '_token' => $form['orob2b_product[_token]']->getValue(),
            'owner'  => $this->getBusinessUnitId(),
            'sku'    => self::TEST_SKU,
            'inventoryStatus' => Product::INVENTORY_STATUS_IN_STOCK,
            'primaryUnitPrecision' => [
                'unit'      => self::FIRST_UNIT_CODE,
                'precision' => self::FIRST_UNIT_PRECISION
            ],
            'additionalUnitPrecisions' => [

                [
                    'unit'      => self::SECOND_UNIT_CODE,
                    'precision' => self::SECOND_UNIT_PRECISION
                ]
            ],
            'prices' => [
                [
                    'priceList' => $priceList->getId(),
                    'price'     => [
                        'value'    => self::FIRST_PRICE_VALUE,
                        'currency' => self::FIRST_PRICE_CURRENCY
                    ],
                    'quantity'  => self::FIRST_QUANTITY,
                    'unit'      => self::FIRST_UNIT_CODE
                ]
            ],
            'status' => Product::STATUS_ENABLED
        ];

        $formData['names']['values']['default'] = self::DEFAULT_NAME;
        foreach ($localizations as $localization) {
            $formData['names']['values']['localizations'][$localization->getId()]['fallback'] = FallbackType::SYSTEM;
        }

        $crawler = $this->client->request($form->getMethod(), $form->getUri(), [
            'input_action'        => 'save_and_stay',
            'orob2b_product' => $formData
        ]);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Product has been saved', $crawler->html());

        $this->assertEquals(
            $priceList->getId(),
            $crawler->filter('input[name="orob2b_product[prices][0][priceList]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::FIRST_QUANTITY,
            $crawler->filter('input[name="orob2b_product[prices][0][quantity]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::FIRST_UNIT_FULL_NAME,
            $crawler->filter('select[name="orob2b_product[prices][0][unit]"] :selected')->html()
        );
        $this->assertEquals(
            self::FIRST_PRICE_VALUE,
            $crawler->filter('input[name="orob2b_product[prices][0][price][value]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::FIRST_PRICE_CURRENCY,
            $crawler->filter('select[name="orob2b_product[prices][0][price][currency]"] :selected')
                ->extract('value')[0]
        );

        /** @var Product $product */
        $product = $this->getContainer()->get('doctrine')
            ->getManagerForClass('OroB2BProductBundle:Product')
            ->getRepository('OroB2BProductBundle:Product')
            ->findOneBy(['sku' => self::TEST_SKU]);
        $this->assertNotEmpty($product);

        return $product->getId();
    }

    /**
     * @depends testCreate
     * @param $id int
     * @return integer
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request('GET', $this->getUrl('orob2b_product_update', ['id' => $id]));

        /** @var PriceList $priceList */
        $priceList = $this->getReference(self::PRICE_LIST_NAME);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orob2b_product[sku]'] = self::TEST_SKU;
        $form['orob2b_product[prices][0][priceList]'] = $priceList->getId();
        $form['orob2b_product[prices][0][quantity]'] = self::SECOND_QUANTITY;
        $form['orob2b_product[prices][0][unit]'] = self::SECOND_UNIT_CODE;
        $form['orob2b_product[prices][0][price][value]'] = self::SECOND_PRICE_VALUE;
        $form['orob2b_product[prices][0][price][currency]'] = self::SECOND_PRICE_CURRENCY;

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Product has been saved', $crawler->html());

        $crawler = $this->client->request('GET', $this->getUrl('orob2b_product_update', ['id' => $id]));

        $this->assertEquals(
            $priceList->getId(),
            $crawler->filter('input[name="orob2b_product[prices][0][priceList]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::EXPECTED_SECOND_QUANTITY,
            $crawler->filter('input[name="orob2b_product[prices][0][quantity]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::SECOND_UNIT_FULL_NAME,
            $crawler->filter('select[name="orob2b_product[prices][0][unit]"] :selected')->html()
        );
        $this->assertEquals(
            self::SECOND_PRICE_VALUE,
            $crawler->filter('input[name="orob2b_product[prices][0][price][value]"]')->extract('value')[0]
        );
        $this->assertEquals(
            self::SECOND_PRICE_CURRENCY,
            $crawler->filter('select[name="orob2b_product[prices][0][price][currency]"] :selected')
                ->extract('value')[0]
        );

        return $id;
    }

    /**
     * @depends testUpdate
     * @param integer $id
     */
    public function testDelete($id)
    {
        $crawler = $this->client->request('GET', $this->getUrl('orob2b_product_update', ['id' => $id]));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        unset($form['orob2b_product[prices]']);

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Product has been saved', $crawler->html());

        $crawler = $this->client->request('GET', $this->getUrl('orob2b_product_update', ['id' => $id]));

        $this->assertContains('orob2b_product[additionalUnitPrecisions][0]', $crawler->html());
        $this->assertNotContains('orob2b_product[prices][0]', $crawler->html());
    }

    /**
     * @return Localization[]
     */
    protected function getLocalizations()
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass('OroLocaleBundle:Localization')
            ->getRepository('OroLocaleBundle:Localization')
            ->findAll();
    }

    /**
     * @return integer
     */
    protected function getBusinessUnitId()
    {
        return $this->getContainer()->get('security.token_storage')->getToken()->getUser()->getOwner()->getId();
    }
}