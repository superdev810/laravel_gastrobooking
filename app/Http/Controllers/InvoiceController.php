<?php

namespace App\Http\Controllers;

use App\Entities\Invoice;
use App\Repositories\InvoiceRepository;
use App\Entities\InvoiceSetting;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Webpatser\Uuid\Uuid;
use Dingo\Api\Routing\Helpers;
use PDF;
use Illuminate\Support\Facades\Mail;
//use Illuminate\Validation\Validator;
//use Illuminate\Foundation\Validation\ValidatesRequests;

class InvoiceController extends Controller
{
    use Helpers;
    protected $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function getInvoiceNumber(Request $request){
        $invoice_number = $this->invoiceRepository->getInvoiceNumber($request->restaurant_id);
        return $invoice_number;
    }

    public function getRestaurantInvoices(Request $request){
        $invoices = Invoice::with(['restaurant' => function($query){
            $query->with('user');
        }])->with('paymentSum')->where('ID_restaurant', $request->restaurant_id)->get();
        return $invoices;
    }

    public function setInvoice(Request $request){

        $response = [
            "success" => false,
            "message" => "",
        ];

        $invoice = array(
            "ID" => $request->invoice_number ? $request->invoice_number : null,                // TODO Need to check if invoice ID is AUTO_INCREMENT delete this line
            "ID_restaurant" => $request->restaurant['id'] ? $request->restaurant['id'] : null,
            "ID_user" => $request->user['id'] ? $request->user['id'] : null,
            "invoice_number" => $request->invoice_number ? $request->invoice_number : null,
            "invoice_taxable" => $request->invoice_taxable ? $request->invoice_taxable : null,
            "invoice_due" => $request->invoice_due ? $request->invoice_due : null,
            "invoice_date" => $request->issue_date ? $request->issue_date : null,
            "payment_form" => $request->payment_form ? $request->payment_form : 1,
            "subject_text" => $request->subject_text ? $request->subject_text : null,
            "invoice_value" => $request->invoice_value ? $request->invoice_value : 0.00,
            "VAT" => $request->vat ? $request->vat : 0.00,
            "note" => $request->note ? $request->note : null,
            "signature_label" => $request->signature ? $request->signature : 0
        );

        $invoice_number = (int)$request->invoice_number;
        $restaurant_id = $request->restaurant['id'];

        if($invoice_number <= (int)$restaurant_id*1000 + 999 && $invoice_number > (int)$restaurant_id*1000 && is_int($invoice_number)) {
            if($this->invoiceRepository->setInvoice($invoice)) {
                if($this->invoiceRepository->setInvoicePayment($request)) {
                    $response['success'] = true;
                    $response['message'] = "Invoise saved";
                    return $response;
                }
            } else{
                $response['message'] = "Invoise not saved SQL Error";
                return $response;
            }
        } else{
            $response['message'] = "Invoise number Error";
            return $response;
        }
    }

    public function exportToPdfSendEmail(Request $request){ // TODO Need to check
        $def_lang = 'ENG';
        $lang = $request->lang ? $request->lang : 'ENG';

        $logged_user = app('Dingo\Api\Auth\Auth')->user();

        $invoice = Invoice::with(['restaurant', 'payments'])->find($request->invoice_id);
//        return $invoice;
        $invoiceSetting = InvoiceSetting::where('lang', '=', $lang)
            ->first();
        if(empty($invoiceSetting)){
            $invoiceSetting = InvoiceSetting::where('lang', '=', $def_lang)
                ->first();
        }

        $data = [
            'invoice' => $invoice,
            'invoiceSetting' => $invoiceSetting,
            'logged_user' => $logged_user
        ];

        $pdf = PDF::loadView('invoice.invoice_to_pdf', $data);

//        if(!empty($request->to_email)){
//            $user = $logged_user;
//            Mail::send('emails.invoice', ['user' => $user], function($message)use($pdf, $invoice, $request){
////                $message->to($request->to_email)->subject('Gastro Booking invoce');
//
//                $message->from('cesko@gastro-booking.com', "Gastro Booking");
//
//                $message->attachData($pdf->output(), 'invoice_'. $invoice->invoice_number .'.pdf');
//
//            });
//        }

        return $pdf->output();
    }

    public function getAllInvoices(Request $request){
        $logged_user = app('Dingo\Api\Auth\Auth')->user();
        $invoices = Invoice::with(['restaurant' => function($query){
            $query->with('user');
        }])->with('paymentSum')->where("ID_user", $logged_user->id)->get();
        return $invoices;
    }
}
