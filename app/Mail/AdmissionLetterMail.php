<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionLetterMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function build()
    {
        $application = $this->data['application'];
        $personal = $this->data['personal'];
        
        $pdf = PDF::loadView('pdf.admission-letter', $this->data);
        
        return $this->subject('Admission Letter - ' . config('app.name'))
            ->view('emails.admission-letter')
            ->with([
                'applicantName' => $personal->first_name . ' ' . $personal->last_name,
                'applicationNumber' => $application->application_number,
                'programName' => $this->data['program']->name ?? 'N/A'
            ])
            ->attachData($pdf->output(), 
                "Admission-Letter-{$application->application_number}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}