<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class Report
{
    public static function generatePDFFromHTML($html)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('report.pdf', array('Attachment' => 0));
    }
}
