<?php

namespace Ekkode\UBL\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test an UBL2.1 invoice document
 */
class EN16931Test extends TestCase
{
    private $schema = 'http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd';
    private $xslfile = 'vendor/ggavrilut/ubl-invoice/tests/EN16931-UBL-validation.xslt';

    /** @test */
    public function testIfXMLIsValid()
    {
        // Tax scheme
        $taxScheme = (new \Ekkode\UBL\TaxScheme())
            ->setId('VAT');

        // Address country
        $country = (new \Ekkode\UBL\Country())
            ->setIdentificationCode('BE');

        // Full address
        $address = (new \Ekkode\UBL\Address())
            ->setStreetName('Korenmarkt 1')
            ->setAdditionalStreetName('Building A')
            ->setCityName('Gent')
            ->setPostalZone('9000')
            ->setCountry($country);

        $financialInstitutionBranch = (new \Ekkode\UBL\FinancialInstitutionBranch())
            ->setId('RABONL2U');

        $payeeFinancialAccount = (new \Ekkode\UBL\PayeeFinancialAccount())
           ->setFinancialInstitutionBranch($financialInstitutionBranch)
            ->setName('Customer Account Holder')
            ->setId('NL00RABO0000000000');

        $paymentMeans = (new \Ekkode\UBL\PaymentMeans())
            ->setPayeeFinancialAccount($payeeFinancialAccount)
            ->setPaymentMeansCode(31, [])
            ->setPaymentId('our invoice 1234');


        // Supplier company node
        $supplierLegalEntity = (new \Ekkode\UBL\LegalEntity())
            ->setRegistrationName('Supplier Company Name')
            ->setCompanyId('BE123456789');

        $supplierPartyTaxScheme = (new \Ekkode\UBL\PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId('BE123456789');

        $supplierCompany = (new \Ekkode\UBL\Party())
            ->setName('Supplier Company Name')
            ->setLegalEntity($supplierLegalEntity)
            ->setPartyTaxScheme($supplierPartyTaxScheme)
            ->setPostalAddress($address);

        // Client company node
        $clientLegalEntity = (new \Ekkode\UBL\LegalEntity())
            ->setRegistrationName('Client Company Name')
            ->setCompanyId('Client Company Registration');

        $clientPartyTaxScheme = (new \Ekkode\UBL\PartyTaxScheme())
            ->setTaxScheme($taxScheme)
            ->setCompanyId('BE123456789');

        $clientCompany = (new \Ekkode\UBL\Party())
            ->setName('Client Company Name')
            ->setLegalEntity($clientLegalEntity)
            ->setPartyTaxScheme($clientPartyTaxScheme)
            ->setPostalAddress($address);

        $legalMonetaryTotal = (new \Ekkode\UBL\LegalMonetaryTotal())
            ->setPayableAmount(10 + 2.1)
            ->setAllowanceTotalAmount(0)
            ->setTaxInclusiveAmount(10 + 2.1)
            ->setLineExtensionAmount(10)
            ->setTaxExclusiveAmount(10);

        $classifiedTaxCategory = (new \Ekkode\UBL\ClassifiedTaxCategory())
            ->setId('S')
            ->setPercent(21.00)
            ->setTaxScheme($taxScheme);

        // Product
        $productItem = (new \Ekkode\UBL\Item())
            ->setName('Product Name')
            ->setClassifiedTaxCategory($classifiedTaxCategory)
            ->setDescription('Product Description');

        // Price
        $price = (new \Ekkode\UBL\Price())
            ->setBaseQuantity(1)
            ->setUnitCode(\Ekkode\UBL\UnitCode::UNIT)
            ->setPriceAmount(10);

        // Invoice Line tax totals
        $lineTaxTotal = (new \Ekkode\UBL\TaxTotal())
            ->setTaxAmount(2.1);

        // InvoicePeriod
        $invoicePeriod = (new \Ekkode\UBL\InvoicePeriod())
            ->setStartDate(new \DateTime());

        // Invoice Line(s)
        $invoiceLine = (new \Ekkode\UBL\InvoiceLine())
            ->setId(0)
            ->setItem($productItem)
            ->setPrice($price)
            ->setInvoicePeriod($invoicePeriod)
            ->setLineExtensionAmount(10)
            ->setInvoicedQuantity(1);

        $invoiceLines = [$invoiceLine];

        // Total Taxes
        $taxCategory = (new \Ekkode\UBL\TaxCategory())
            ->setId('S', [])
            ->setPercent(21.00)
            ->setTaxScheme($taxScheme);

        $taxSubTotal = (new \Ekkode\UBL\TaxSubTotal())
            ->setTaxableAmount(10)
            ->setTaxAmount(2.1)
            ->setTaxCategory($taxCategory);


        $taxTotal = (new \Ekkode\UBL\TaxTotal())
            ->addTaxSubTotal($taxSubTotal)
            ->setTaxAmount(2.1);

        // Payment Terms
        $paymentTerms = (new \Ekkode\UBL\PaymentTerms())
            ->setNote('30 days net');

        // Delivery
        $deliveryLocation = (new \Ekkode\UBL\Address())
            ->setCountry($country);

        $delivery = (new \Ekkode\UBL\Delivery())
            ->setActualDeliveryDate(new \DateTime())
            ->setDeliveryLocation($deliveryLocation);

        $orderReference = (new \Ekkode\UBL\OrderReference())
            ->setId('5009567')
            ->setSalesOrderId('tRST-tKhM');

        // Invoice object
        $invoice = (new \Ekkode\UBL\Invoice())
            ->setCustomizationID('urn:cen.eu:en16931:2017')
            ->setId(1234)
            ->setIssueDate(new \DateTime())
            ->setNote('invoice note')
            ->setDelivery($delivery)
            ->setAccountingSupplierParty($supplierCompany)
            ->setAccountingCustomerParty($clientCompany)
            ->setInvoiceLines($invoiceLines)
            ->setLegalMonetaryTotal($legalMonetaryTotal)
            ->setPaymentTerms($paymentTerms)
            ->setInvoicePeriod($invoicePeriod)
            ->setPaymentMeans($paymentMeans)
            ->setBuyerReference('BUYER_REF')
            ->setOrderReference($orderReference)
            ->setTaxTotal($taxTotal);

        // Test created object
        // Use \Ekkode\UBL\Generator to generate an XML string
        $generator = new \Ekkode\UBL\Generator();
        $outputXMLString = $generator->invoice($invoice);

        // Create PHP Native DomDocument object, that can be
        // used to validate the generate XML
        $dom = new \DOMDocument;
        $dom->loadXML($outputXMLString);

        $dom->save('./tests/EN16931Test.xml');

        // $this->assertEquals(true, $dom->schemaValidate($this->schema));

        // Use webservice at peppol.helger.com to verify the result
        $wsdl = "http://peppol.helger.com/wsdvs?wsdl=1";
        $client = new \SoapClient($wsdl);
        $response = $client->validate(['XML' => $outputXMLString, 'VESID' => 'eu.cen.en16931:ubl:1.3.1']);

        // Output validation warnings if present
        if ($response->mostSevereErrorLevel == 'WARN' && isset($response->Result[1]->Item)) {
            foreach ($response->Result[1]->Item as $responseWarning) {
                fwrite(STDERR, '*** '.$responseWarning->errorText."\n");
            }
        }

        $this->assertEquals('SUCCESS', $response->mostSevereErrorLevel);
    }
}
