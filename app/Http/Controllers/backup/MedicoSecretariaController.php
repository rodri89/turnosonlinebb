<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SecretariaConsultorio;
use App\User;
use App\Paciente;
use App\Medico;
use App\Secretaria;
use App\TurnoRegistrado;
use App\TurnoRegistradoVideollamada;
use App\PacienteSecretaria;
use DateTime;
use \stdClass;
use Mail;
use App\Mail\ActivarPacienteMailable;
use Datatables;

class MedicoSecretariaController extends Controller
{
    
}
