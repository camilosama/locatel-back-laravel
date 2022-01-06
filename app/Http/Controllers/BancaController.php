<?php

namespace App\Http\Controllers;
//Funciones
use Illuminate\Http\Request;
use DB;
//MODELOS
use App\banca;

class BancaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crearCuenta(Request $request){


        $digits = 8;
        $number=rand(pow(10, $digits-1), pow(10, $digits)-1);

        $nombreResp = $request->input('nomPersp');

        try {
            DB::table('CUENTA')->insert([
                'CUENTANOM' => "$nombreResp", 
                'CUENTANUM' => $number,
                'VALORINICIAL' => $request->input('valorI'), 
                'VALORACTUAL' => $request->input('valorI'), 
            ]);
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            return 'Problema al insertar los datos por favor verifique estos datos. [CUENTA]';
        }

        DB::table('HISTORIAL')->insert([
            'TIPOT' => "Apertura", 
            'CUENTANUM' => $number,
            'VALOR' => $request->input('valorI'), 
            'FECHA' => date('Y-m-d H:i:s'), 
        ]);
    

        DB::commit();
        return 'Dato Registrado de forma correcta, su cuenta fue registrada bajo el numero <br>'.$number;
    }
    public function consignar(Request $request){

        $cuentaConsultada = DB::table('CUENTA')
                ->select('VALORACTUAL')
                ->where("CUENTANUM",$request->input('numCuenta'))
                ->first();
                
        $actualValor=$cuentaConsultada->VALORACTUAL;
        $actualValor=$actualValor+$request->input('valorC');

        try {
            DB::table('CUENTA')
                ->where('CUENTANUM', $request->input('numCuenta'))
                ->update(['VALORACTUAL' => $actualValor]);
        } catch (\Exception $e) {
            DB::rollback();
            return 'Problema al actualizar el numero a consignar en el sistema';
        }

        DB::table('HISTORIAL')->insert([
            'TIPOT' => "Consignacion", 
            'CUENTANUM' => $request->input('numCuenta'),
            'VALOR' => $actualValor, 
            'FECHA' => date('Y-m-d H:i:s'), 
        ]);
    

        DB::commit();
        return 'Consignacion registrada de forma correcta <br> Acutal saldo: '.$actualValor;

    }

    public function RetirarDinero(Request $request){
        $cuentaConsultada = DB::table('CUENTA')
                ->select('VALORACTUAL')
                ->where("CUENTANUM",$request->input('numCuenta'))
                ->first();
                
        $actualValor=$cuentaConsultada->VALORACTUAL;

        if($actualValor < $request->input('valorR')){
            return 'El valor a retirar es mayor al poseido en la cuenta. Recuerde que su actual saldo es de: '.$actualValor;
        }

        $actualValor=$actualValor-$request->input('valorR');

        try {
            DB::table('CUENTA')
                ->where('CUENTANUM', $request->input('numCuenta'))
                ->update(['VALORACTUAL' => $actualValor]);
        } catch (\Exception $e) {
            DB::rollback();
            return 'Problema al actualizar el numero a consignar en el sistema';
        }

        DB::table('HISTORIAL')->insert([
            'TIPOT' => "Retiro", 
            'CUENTANUM' => $request->input('numCuenta'),
            'VALOR' => $actualValor, 
            'FECHA' => date('Y-m-d H:i:s'), 
        ]);

        DB::commit();
        return 'Retiro registrada de forma correcta <br> Acutal saldo: '.$actualValor;

    }
    //Estraer datos de la cuenta
    public function datosCuenta(Request $request){

        $datosCuenta = DB::table('CUENTA')
        ->select('CUENTANOM','VALORINICIAL','VALORACTUAL')
        ->where('CUENTANUM', $request->input('valor'))
        ->get();
        
        $historialCuenta= DB::table('HISTORIAL')
            ->select('CUENTANUM','TIPOT','VALOR','FECHA')
            ->where('CUENTANUM', $request->input('valor'))
            ->orderBy('FECHA')
            ->get();

        $data=array(
            "datosCuenta"=>$datosCuenta,
            "historialCuenta"=>$historialCuenta,
        );

        return $data;
    }

    public function listaCuentas(){

        $listaCuentas= DB::table('CUENTA')
            ->select('CUENTANUM','CUENTANOM')
            ->orderBy('CUENTANOM')
            ->get();

        $data=array(
            "listaCuentas"=>$listaCuentas,
        );

        return $data;
    }

    

   
}
