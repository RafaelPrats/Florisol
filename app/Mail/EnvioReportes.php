<?php

namespace yura\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnvioReportes extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre_archivo;
    public $nombre_reporte;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre_archivo, $nombre_reporte)
    {
        $this->nombre_archivo = $nombre_archivo;
        $this->nombre_reporte = $nombre_reporte;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('adminlte.gestion.mails.reportes.envio_reporte')
            ->subject('Reportes del sistema')
            //->cc($this->cc)
            ->attach(public_path() . '/storage/files_mail/' . $this->nombre_archivo);
    }
}
