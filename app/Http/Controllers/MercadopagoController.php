<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Services\CurrencyConversionService;
use Illuminate\Support\Facades\DB;
use App\TurnoRegistradoVideollamada;
use App\Medico;

class MercadopagoController extends Controller
{

     public function pay(Request $request)
    {   
        $turno_id = $request->turno_id;
        $medico_id = $request->medico_id;
        
        $cred_mp = DB::table('videollamadas')                                                                
                ->where('videollamadas.medico', $medico_id)
                ->where('videollamadas.activo', 1)                                                      
                ->get();         

        $turno = TurnoRegistradoVideollamada::find($turno_id);

        $paciente = DB::table('pacientes')                                                                
                ->where('pacientes.id', $turno->paciente)                                                      
                ->where('pacientes.activo', 1)                                                      
                ->get();        
             
        if($cred_mp[0] != null) {
            $key = $cred_mp[0]->key;
            $secret = $cred_mp[0]->secret;
            $value = $cred_mp[0]->importe;
            $payment = (new MercadoPagoService(new CurrencyConversionService(), $key, $secret, $value))->handlePayment($request);
            if($payment->status === "approved") {        
                $turno->pago = 1;
                $turno->save();            
                //$this->asistirVideollamada($medico_id, $paciente[0]->dni, $turno_id);
                
                return $this->asistirVideollamada($medico_id, $paciente[0]->dni, $turno_id, $payment);//view('turnos.detalles_mercadopago');

            } else {
                return $this->asistirVideollamada($medico_id, $paciente[0]->dni, $turno_id, $payment);
            }
        }
        //return $paymentPlatform->handlePayment($request);
    }

    public function asistirVideollamada($medico_id, $dni_paciente, $turno_id, $payment){

        $paciente = DB::table('pacientes')
                         ->where('pacientes.dni', $dni_paciente)
                         ->where('pacientes.activo', 1)                         
                         ->first();    
        $turnoRegistrado = DB::table('turno_registrado_videollamadas')
                         ->where('turno_registrado_videollamadas.id', $turno_id)
                         ->where('turno_registrado_videollamadas.activo', 1)                         
                         ->first();    

        $medico = medico::find($turnoRegistrado->medico);
        $horario = $turnoRegistrado->horario;
        $fecha_aux = explode('-',$turnoRegistrado->fechaTurno);
        $fecha = $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];
        $videollamada = DB::table('videollamadas')
                         ->where('videollamadas.medico', $medico->id)
                         ->where('videollamadas.activo', 1)                         
                         ->first();   

        $tieneObraSocialCompleta = 0;
        if(strcmp($paciente->obra_social, 'PARTICULAR') == 0){
            $tieneObraSocialCompleta = 0;
        } else {
            if(strcmp($paciente->obra_social_foto, '') != 0) {
                $tieneObraSocialCompleta = 1;
            }
        }
        
        $moduloMercadoPago = 0;
        if($this->moduloActivo($medico->id, 7) == 1)  
            $moduloMercadoPago = 1;
        
        $medicoTrabajaConObraSocial = $this->medicoTrabajaConObraSocial($medico, $paciente);

        return view('turnos.videollamadas')
                    ->with('turnoRegistrado',$turnoRegistrado)
                    ->with('paciente',$paciente)
                    ->with('medico',$medico)
                    ->with('tieneObraSocialCompleta',$tieneObraSocialCompleta)
                    ->with('moduloMercadoPago',$moduloMercadoPago)
                    ->with('videollamada',$videollamada)
                    ->with('fechaTurno',$fecha)
                    ->with('medicoTrabajaConObraSocial',$medicoTrabajaConObraSocial)
                    ->with('payment',$payment)
                    ->with('horario',$horario);
    }

    public function medicoTrabajaConObraSocial($medico, $paciente){
        $obrasSocialesMedico = DB::table('obra_social_medicos')
                                ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')                          
                                ->where('obra_social_medicos.medico', $medico->id)
                                ->where('obra_socials.nombre', $paciente->obra_social)
                                ->get();
        if($obrasSocialesMedico->count()>0)
            return 1;
        else
            return 0;
    }

    public function moduloActivo($medico_id, $modulo_id){
         $moduloActivoAux = DB::table('modulo_medicos')
                        ->where('modulo_medicos.medico', $medico_id)
                        ->where('modulo_medicos.modulo', $modulo_id) // 1 corresponde a ACtivar pacientes
                        ->where('modulo_medicos.activo', 1)
                        ->first();
        $moduloActivo = 0;
        if($moduloActivoAux != null)
            $moduloActivo = 1;
        
        return $moduloActivo;
    }

    public function mercadoPagoTransaccion($res){
        return view('turnos.mercado_pago_transaccion');
    }

    // mercado pago no utiliza este metodo.
    public function approval()
    {
        /*if (session()->has('paymentPlatformId')) {
            $paymentPlatform = $this->paymentPlatformResolver
                ->resolveService(session()->get('paymentPlatformId'));
        
            return $paymentPlatform->handleApproval();*/
        return  (new MercadoPagoService(new CurrencyConversionService()))->handleApproval();
        //}

        return redirect()
            ->route('home')
            ->withErrors('We cannot retrieve your payment platform. Try again, plase.');
    }

    public function cancelled()
    {
        return redirect()
            ->route('home')
            ->withErrors('You cancelled the payment');
    }

    public function guardarNumeroOperacion(Request $request){
        $turno = TurnoRegistradoVideollamada::find($request->turno_id);        
        $turno->pago_ticket = $request->numero_operacion;
        $turno->save();

        return response()->json(array('response'=>1, 'videollamada'=>$turno));
    }


    public function procesarPago(){
           require_once 'vendor/autoload.php';

    MercadoPago\SDK::setAccessToken("ENV_ACCESS_TOKEN");

    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = 144;
    $payment->token = "ff8080814c11e237014c1ff593b57b4d";
    $payment->description = "Durable Granite Plate";
    $payment->installments = 1;
    $payment->payment_method_id = "visa";
    $payment->payer = array(
    "email" => "johnpaul@yahoo.com"
    );

    $payment->save();


    echo $payment->status;
    }

    public function createPreference(Request $request)
    {
        //\MercadoPago\SDK::setAccessToken(config('services.mercadopago.access_token'));        
        $token = 'APP_USR-2894725685669649-041419-48f85ff9f0f5a8815e427ae78e20dc8f-214444922';
        //$token = 'TEST-104399887932260-033121-40b8ed55cfc0f16da09e4e99c310d6ca-206496366';
        \MercadoPago\SDK::setAccessToken($token);

        $preference = new \MercadoPago\Preference();

        $item = new \MercadoPago\Item();
        $item->title = $request->title ?? 'Producto genérico';
        $item->quantity = 1;
        $item->unit_price = (float) $request->amount;

        $preference->items = [$item];

        // Estas URLs deben coincidir con lo que vas a detectar en la app
        $preference->back_urls = [
            "success" => "https://redirect.com/success",
            "failure" => "https://redirect.com/failure",
            "pending" => "https://redirect.com/pending",
        ];

        $preference->auto_return = "approved";

        $preference->save();

        return response()->json([
            'preference_id' => $preference->id,
            'init_point' => $preference->init_point,
        ]);
    }

}
