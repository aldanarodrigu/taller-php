<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Repositories\ProfesionalRepository;
use App\Models\Profesional;
use App\Models\User;
use Exception;

class ProfesionalService{

    private ProfesionalRepository $profesionalRepository;

    public function __construct(ProfesionalRepository $profesionalRepository){
        $this->profesionalRepository = $profesionalRepository;
    }

    public function editarProfesional(User $user, Request $request)
    {
        $profesional = $this->profesionalRepository
            ->findByUserId($user->id);

        $datos = [
            'descripcion' => $request->descripcion,
            'profesion' => $request->profesion,
            'modalidad_atencion' => $request->modalidad_atencion,
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $nombre = time() . '.' . $file->getClientOriginalExtension();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.supabase.service_key'),
                'Content-Type' => $file->getMimeType(),
            ])->withBody(
                file_get_contents($file->getRealPath()),
                $file->getMimeType()
            )->post(
                config('services.supabase.url') . '/storage/v1/object/profesionales/' . $nombre
            );

            if ($response->successful()) {
                $datos['foto'] = config('services.supabase.url') 
                    . '/storage/v1/object/public/profesionales/' 
                    . $nombre;
            }
        }

        $profesional->update($datos);

        return $profesional;
    }
}

