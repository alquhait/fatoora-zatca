<?php

namespace Bl\FatooraZatca\Services;

use Bl\FatooraZatca\Actions\PostRequestAction;
use Bl\FatooraZatca\Services\Invoice\HashInvoiceService;
use Bl\FatooraZatca\Services\Invoice\SignInvoiceService;

class ReportInvoiceService
{
    /**
     * the seller data.
     *
     * @var object
     */
    protected $seller;

    /**
     * the invoice data.
     *
     * @var object
     */
    protected $invoice;

    /**
     * the client data.
     *
     * @var object
     */
    protected $client;

    /**
     * __construct
     *
     * @param  object       $seller
     * @param  object       $invoice
     * @param  object|null  $client
     * @return void
     */
    public function __construct(object $seller, object $invoice, object $client = null)
    {
        $this->seller   = $seller;

        $this->invoice  = $invoice;

        $this->client   = $client;
    }

    /**
     * share the invoice with zatca portal.
     *
     * @return array
     */
    public function reporting(): array
    {
        $route = '/invoices/reporting/single';

        return $this->report($route, '0200000');
    }

    /**
     * clearance the invoice from zatca portal.
     *
     * @return array
     */
    public function clearance(): array
    {
        $route = '/invoices/clearance/single';
        // $route = '/compliance/invoices';

        return $this->report($route, '0100000');
    }

    /**
     * report the invoice to zatca.
     *
     * @param  string $route
     * @param  string $document_type
     * @return array
     */
    public function report(string $route, string $document_type): array
    {
        $hashInvoiceService = new HashInvoiceService($this->seller, $this->invoice, $this->client);

        $invoiceHash = $hashInvoiceService->generate($document_type);

        $signedXmlContent = (new SignInvoiceService(
            $this->seller,
            $this->invoice,
            $hashInvoiceService->getInvoiceXmlContent(),
            $invoiceHash
        ))->generate();


        $USERPWD = $this->seller->certificate . ':' . $this->seller->secret;

        $response = (new PostRequestAction)->handle($route,
        [
            'invoiceHash' => $invoiceHash, # hashed invoice in base64 format
            'uuid' => $this->invoice->invoice_uuid,
            'invoice' => $signedXmlContent, # signed invoice in base64 format
        ],
        [
            'Content-Type: application/json',
            'Accept-Language: en',
            'Accept-Version: V2',
            'Clearance-Status: 1'
        ],
            $USERPWD
        );

        dd($response);
    }
}