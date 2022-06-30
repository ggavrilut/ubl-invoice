<?php

namespace Ekkode\UBL\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test an UBL2.2 invoice document
 */
class SimpleUBL22InvoiceTest extends TestCase
{
    private $schema = 'http://docs.oasis-open.org/ubl/os-UBL-2.2/xsd/maindoc/UBL-Invoice-2.2.xsd';

    /** @test */
    public function testIfXMLIsValid()
    {
        // Address country
        $country = (new \Ekkode\UBL\Country())
            ->setIdentificationCode('BE');

        // Full address
        $address = (new \Ekkode\UBL\Address())
            ->setStreetName('Korenmarkt')
            ->setBuildingNumber(1)
            ->setCityName('Gent')
            ->setPostalZone('9000')
            ->setCountry($country);

        // Supplier company node
        $supplierCompany = (new \Ekkode\UBL\Party())
            ->setName('Supplier Company Name')
            ->setPhysicalLocation($address)
            ->setPostalAddress($address);

        // Client company node
        $clientCompany = (new \Ekkode\UBL\Party())
            ->setName('My client')
            ->setPostalAddress($address);

        $legalMonetaryTotal = (new \Ekkode\UBL\LegalMonetaryTotal())
            ->setPayableAmount(10 + 2)
            ->setAllowanceTotalAmount(0);

        // Tax scheme
        $taxScheme = (new \Ekkode\UBL\TaxScheme())
            ->setId(0);

        // Product
        $productItem = (new \Ekkode\UBL\Item())
            ->setName('Product Name')
            ->setDescription('Product Description');

        // Price
        $price = (new \Ekkode\UBL\Price())
            ->setBaseQuantity(1)
            ->setUnitCode(\Ekkode\UBL\UnitCode::UNIT)
            ->setPriceAmount(10);

        // Invoice Line tax totals
        $lineTaxTotal = (new \Ekkode\UBL\TaxTotal())
            ->setTaxAmount(2.1);

        // Invoice Line(s)
        $invoiceLine = (new \Ekkode\UBL\InvoiceLine())
            ->setId(0)
            ->setItem($productItem)
            ->setPrice($price)
            ->setTaxTotal($lineTaxTotal)
            ->setInvoicedQuantity(1);

        $invoiceLines = [$invoiceLine];

        // Total Taxes
        $taxCategory = (new \Ekkode\UBL\TaxCategory())
            ->setId(0)
            ->setName('VAT21%')
            ->setPercent(.21)
            ->setTaxScheme($taxScheme);

        $taxSubTotal = (new \Ekkode\UBL\TaxSubTotal())
            ->setTaxableAmount(10)
            ->setTaxAmount(2.1)
            ->setTaxCategory($taxCategory);

        $taxTotal = (new \Ekkode\UBL\TaxTotal())
            ->addTaxSubTotal($taxSubTotal)
            ->setTaxAmount(2.1);

        // Invoice object
        $invoice = (new \Ekkode\UBL\Invoice())
            ->setUBLVersionID('2.2')
            ->setId(1234)
            ->setCopyIndicator(false)
            ->setIssueDate(new \DateTime())
            ->setAccountingSupplierParty($supplierCompany)
            ->setAccountingCustomerParty($clientCompany)
            ->setInvoiceLines($invoiceLines)
            ->setLegalMonetaryTotal($legalMonetaryTotal)
            ->setTaxTotal($taxTotal);

        // Test created object
        // Use \Ekkode\UBL\Generator to generate an XML string
        $generator = new \Ekkode\UBL\Generator();
        $outputXMLString = $generator->invoice($invoice);

        // Create PHP Native DomDocument object, that can be
        // used to validate the generate XML
        $dom = new \DOMDocument;
        $dom->loadXML($outputXMLString);

        $dom->save('./tests/SimpleUBL22InvoiceTest.xml');

        $this->assertEquals(true, $dom->schemaValidate($this->schema));
    }
}
